<?php

namespace App\Http\Controllers;

use App\Models\Runsa;
use Illuminate\Http\Request;

class RunsaController extends Controller
{
    use Illuminate\Support\Facades\Http;

function consumirApi($url, $method = 'GET', $data = [], $token = null)
{
    try {
        $client = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $token ? "Bearer $token" : null,
        ]);

        // Selección del método dinámico
        switch (strtoupper($method)) {
            case 'POST':
                $response = $client->post($url, $data);
                break;
            case 'PUT':
                $response = $client->put($url, $data);
                break;
            case 'DELETE':
                $response = $client->delete($url, $data);
                break;
            default: // GET
                $response = $client->get($url, $data);
                break;
        }

        if ($response->successful()) {
            return $response->json(); // Devuelve la respuesta como array
        }

        return [
            'error' => true,
            'status' => $response->status(),
            'message' => $response->body(),
        ];

    } catch (\Exception $e) {
        return [
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }
}



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        //return $request;
       $q =Runsa::where('productor_id',$request->productor_id)->orderBy('id', 'desc');

        if ($request->boolean('paginate', true)) {
            $per = max(10, min((int) $request->get('per_page', 50), 200));
            return $q->paginate($per)->appends($request->query());
        }

        return $q->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'codigo' => ['required','string','max:50'],
            'subcodigo' => ['required','string','max:50'],
            'fecha_registro' => ['required','date'],
            'fecha_vencimiento' => ['required','date'],
            'estado' => ['required','string','max:20'],
            'productor_id' => ['required','integer','exists:productores,id'],
        ]);
        $runsa = Runsa::create($data);
        return $runsa;

    }

    /**
     * Display the specified resource.
     */
    public function show(Runsa $runsa)
    {
        //
        return $runsas->load('productor:id,nombre,apellidos,numcarnet');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Runsa $runsa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Runsa $runsa)
    {
        //
        $data = $request->validate([
            'codigo' => ['required','string','max:50'],
            'subcodigo' => ['required','string','max:50'],
            'fecha_registro' => ['required','date'],
            'fecha_vencimiento' => ['required','date'],
            'estado' => ['required','string','max:20'],
            'productor_id' => ['required','integer','exists:productores,id'],
        ]);
        $runsa->update($data);
        return $runsa;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Runsa $runsa)
    {
        //
        $runsa->delete();
        return response()->json(['deleted' => true]);
    }
}
