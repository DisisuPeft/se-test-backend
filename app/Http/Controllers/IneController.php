<?php

namespace App\Http\Controllers;

use App\Http\Requests\IneRequest;
use App\Services\IneService;
use Illuminate\Http\Request;
use App\Models\Empleados;

class IneController extends Controller
{
    public function extract(IneRequest $request, IneService $service){
    //    dd($request);
        $data = $service->process($request->file('file'));
        //localidad no pudo se extraida, si hay tiempo habria que trabajar en revisar detenidamente como extraerla o algoritmoas mas avanzado en python
        // dd($data);

        $ciudad = null;
        $estado = null;
            
        if (isset($data['domicilio']) && preg_match('/([A-Z\s]+),\s*([A-Z]+)/', $data['domicilio'], $matches)) {
            $ciudad = trim($matches[1]);
            $estado = trim($matches[2]);
        }
            
        Empleados::create([
            'nombre' => $data['nombre'] ?? null,
            'apellidos' => ($data['apellido_paterno'] ?? '') . " " . ($data['apellido_materno'] ?? ''),
            'curp' => $data['curp'] ?? null,
            'estado' => $estado,
            'municipio' => $ciudad,
            'localidad' => null,
        ]);
        
 
        return response()->json([
            'ok' => true,
            'data' => $data,
        ], 200);
    }

    public function getColab(){
        $colab = Empleados::all();

        return response()->json([
            'colab' => $colab
        ]);
    }
}
