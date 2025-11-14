<?php

namespace App\Http\Controllers;

use App\Mail\UserCreatedMail;
use App\Models\BpUsuarios;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\Permission\Models\Permission;

class UserController extends Controller{
    public function updateAvatar(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = public_path('images/' . $filename);

            // Crear instancia del gestor de imágenes
            $manager = new ImageManager(new Driver()); // O new Imagick\Driver()

            // Redimensionar y comprimir
            $manager->read($file->getPathname())
                ->resize(300, 300) // o no pongas resize si no quieres cambiar tamaño
                ->toJpeg(70)       // calidad 70%
                ->save($path);

            $user->avatar = $filename;
            $user->save();

            return response()->json(['message' => 'Avatar actualizado', 'avatar' => $filename]);
        }

        return response()->json(['message' => 'No se ha enviado un archivo'], 400);
    }
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // ========= 1) INTENTO CON USUARIO EXTERNO (tabla users) =========
        $user = User::where('username', $credentials['username'])
            ->with('permissions:id,name')
            ->first();

        if ($user) {
            // Si el usuario externo existe, solo aceptamos su propio password
            if (!password_verify($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Usuario o contraseña incorrectos',
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $user,
                'source'=> 'externo',
            ]);
        }

        // ========= 2) SI NO EXISTE EXTERNO, PROBAMOS USUARIO INTERNO =========
        $bpUser = BpUsuarios::where('usr_usuario', $credentials['username'])
            ->where('usr_estado', 'A') // solo activos
            ->first();

        if (!$bpUser) {
            // No existe en internos tampoco
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos',
            ], 401);
        }

        // Verificar password interno
        if (!$this->checkInternalPassword($credentials['password'], $bpUser->password)) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos',
            ], 401);
        }

        // Verificar que tenga acceso al sistema TRAZA
        if (!$this->hasTrazaAccess($bpUser)) {
            return response()->json([
                'message' => 'El usuario no tiene acceso al sistema TRAZA',
            ], 403);
        }

        // Crear token Sanctum para el usuario interno
        $token = $bpUser->createToken('auth_token')->plainTextToken;

        // Armamos un "user" sencillo para el frontend
        $internalUserPayload = [
            'id'         => $bpUser->usr_id,
            'username'   => $bpUser->usr_usuario,
            'name'       => $bpUser->usr_usuario,
            'estado'     => $bpUser->usr_estado,
            'is_internal'=> true,
            'permissions'=> [
                ['id' => 0, 'name' => 'TRAZA'], // si quieres marcar que tiene TRAZA
            ],
        ];

        return response()->json([
            'token' => $token,
            'user'  => $internalUserPayload,
            'source'=> 'interno',
        ]);
    }
    private function checkInternalPassword(string $plain, string $hash): bool
    {
        // crypt usa el hash como "salt"
        return hash_equals($hash, crypt($plain, $hash));
    }

    /**
     * Verifica si el usuario interno tiene activado el sistema TRAZA.
     */
    private function hasTrazaAccess(BpUsuarios $user): bool
    {
        $systems = $user->usr_access_sistem ?? [];

        foreach ($systems as $s) {
            if (($s['sistema'] ?? null) === 'TRAZA') {
                return (bool) ($s['activado'] ?? false);
            }
        }

        return false;
    }

    function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Token eliminado',
        ]);
    }
    function me(Request $request){
        $user = $request->user();
        $user->load('permissions:id,name');
        return response()->json($user);
    }
    function index(){
        return User::where('id', '!=', 0)
            ->with('permissions:id,name')
            ->orderBy('id', 'desc')
            ->get();
    }
    function update(Request $request, $id){
        $user = User::find($id);
        $user->update($request->except('password'));
        error_log('User' . json_encode($user));
        return $user;
    }
    function updatePassword(Request $request, $id){
        $user = User::find($id);
        $user->update([
            'password' => bcrypt($request->password),
        ]);
        return $user;
    }
    function store(Request $request){
        $validatedData = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'name' => 'required',
//            'email' => 'required|email|unique:users',
        ]);
        if (User::where('username', $request->username)->exists()) {
            return response()->json(['message' => 'El nombre de usuario ya existe'], 422);
        }
        $user = User::create($request->all());
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new UserCreatedMail(
                    $user->name,
                    $user->username,
                    $request->password,
                ));
            } catch (\Exception $e) {
//                \Log::error("Error enviando correo de usuario creado: " . $e->getMessage());
                error_log("Error enviando correo de usuario creado: " . $e->getMessage());
            }
        }
        return $user;
    }
    function destroy($id){
        $user = User::find($id);
//        $userAll = User::all();
//        error_log('Users total ' . json_encode($userAll));
//        error_log('Delete User ' . json_encode($user));
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
//        return User::destroy($id);
    }
    public function getPermissions($userId)
    {
        $user = User::findOrFail($userId);
        // devuelve IDs de permisos del usuario
        return $user->permissions()->pluck('id');
    }

    public function syncPermissions(Request $request, $userId)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $user = User::findOrFail($userId);
        $perms = Permission::whereIn('id', $request->permissions ?? [])->get();
        $user->syncPermissions($perms);

        return response()->json([
            'message' => 'Permisos actualizados',
            'permissions' => $user->permissions()->pluck('name'),
        ]);
    }
}
