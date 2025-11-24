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
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function updateAvatar(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($request->hasFile('avatar')) {
            $file     = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path     = public_path('images/' . $filename);

            $manager = new ImageManager(new Driver());

            $manager->read($file->getPathname())
                ->resize(300, 300)
                ->toJpeg(70)
                ->save($path);

            $user->avatar = $filename;
            $user->save();

            return response()->json(['message' => 'Avatar actualizado', 'avatar' => $filename]);
        }

        return response()->json(['message' => 'No se ha enviado un archivo'], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login de usuario externo/interno",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="admin"),
     *             @OA\Property(property="password", type="string", format="password", example="admin123Admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuario o contraseña incorrectos"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // ========= 1) USUARIO EXTERNO =========
        $user = User::where('username', $credentials['username'])
            ->with(['permissions:id,name', 'roles:id,name']) // 👈 también roles
            ->first();

        if ($user) {
            if (!password_verify($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Usuario o contraseña incorrectos',
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token'  => $token,
                'user'   => $user,
                'source' => 'externo',
            ]);
        }

        // ========= 2) USUARIO INTERNO =========
        $bpUser = BpUsuarios::where('usr_usuario', $credentials['username'])
            ->where('usr_estado', 'A')
            ->with(['permissions:id,name', 'roles:id,name'])
            ->first();

        if (!$bpUser) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos',
            ], 401);
        }

        if (!$this->checkInternalPassword($credentials['password'], $bpUser->password)) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos',
            ], 401);
        }

        if (!$this->hasTrazaAccess($bpUser)) {
            return response()->json([
                'message' => 'El usuario no tiene acceso al sistema TRAZA',
            ], 403);
        }

        $token = $bpUser->createToken('auth_token')->plainTextToken;

        $internalUserPayload = [
            'id'          => $bpUser->usr_id,
            'username'    => $bpUser->usr_usuario,
            'name'        => $bpUser->usr_usuario,
            'estado'      => $bpUser->usr_estado,
            'is_internal' => true,
            'permissions' => $bpUser->permissions,
            'roles'       => $bpUser->roles,
        ];

        return response()->json([
            'token'  => $token,
            'user'   => $internalUserPayload,
            'source' => 'interno',
        ]);
    }

    private function checkInternalPassword(string $plain, string $hash): bool
    {
        return hash_equals($hash, crypt($plain, $hash));
    }

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Token eliminado',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('permissions:id,name', 'roles:id,name'); // 👈 también roles
        return response()->json($user);
    }

    public function index()
    {
        return User::where('id', '!=', 0)
            ->with(['permissions:id,name', 'roles:id,name']) // 👈 también roles
            ->orderBy('id', 'desc')
            ->get();
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->only(['name', 'email', 'username', 'avatar']);

        // 👇 Si viene role_id, sincronizamos el rol del usuario
        if ($request->filled('role_id')) {
            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role->id]);
                $data['role'] = $role->name; // columna texto para mostrar
            }
        }

        $user->update($data);

        $user->load('permissions:id,name', 'roles:id,name');

        error_log('User' . json_encode($user));
        return $user;
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'password' => $request->password, // casteado como hashed en el modelo
        ]);
        return $user;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'name'     => 'required',
            'email'    => 'nullable|email',
            'role_id'  => 'nullable|integer|exists:roles,id',
        ]);

        if (User::where('username', $validatedData['username'])->exists()) {
            return response()->json(['message' => 'El nombre de usuario ya existe'], 422);
        }

        $userData = [
            'username' => $validatedData['username'],
            'password' => $validatedData['password'], // hashed por cast
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'] ?? null,
        ];

        // Si viene rol, lo guardamos como texto también
        $role = null;
        if (!empty($validatedData['role_id'])) {
            $role = Role::find($validatedData['role_id']);
            if ($role) {
                $userData['role'] = $role->name;
            }
        }

        $user = User::create($userData);

        // Sincronizar roles Spatie
        if ($role) {
            $user->syncRoles([$role->id]);
        }

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new UserCreatedMail(
                    $user->name,
                    $user->username,
                    $validatedData['password'],
                ));
            } catch (\Exception $e) {
                error_log("Error enviando correo de usuario creado: " . $e->getMessage());
            }
        }

        $user->load('permissions:id,name', 'roles:id,name');

        return $user;
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }

    // ---------- PERMISOS DIRECTOS (puedes dejarlo para compatibilidad) ----------
    public function getPermissions($userId)
    {
        $user = User::findOrFail($userId);
        return $user->permissions()->pluck('id');
    }

    public function syncPermissions(Request $request, $userId)
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $user  = User::findOrFail($userId);
        $perms = Permission::whereIn('id', $request->permissions ?? [])->get();
        $user->syncPermissions($perms);

        return response()->json([
            'message'     => 'Permisos actualizados',
            'permissions' => $user->permissions()->pluck('name'),
        ]);
    }

    // ---------- ROLES DEL USUARIO (NUEVO) ----------
    public function getRoles($userId)
    {
        $user = User::findOrFail($userId);
        return $user->roles()->pluck('id');
    }

    public function syncRoles(Request $request, $userId)
    {
        $request->validate([
            'roles'   => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        error_log('Synchronizing roles for user ID: ' . json_encode($user));

        $roleIds = $request->roles ?? [];
        $user->syncRoles($roleIds);

//        // actualizar columna texto "role" con el primer rol (opcional)
//        $firstRole = $user->roles()->first();
//        if ($firstRole) {
//            $user->role = $firstRole->name;
//            $user->save();
//        }
//
//        return response()->json([
//            'message'     => 'Roles del usuario actualizados',
//            'roles'       => $user->roles()->pluck('name'),
//            'permissions' => $user->getAllPermissions()->pluck('name'),
//        ]);
    }
}
