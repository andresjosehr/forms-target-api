<?php

namespace App\Http\Controllers;

use App\Http\Requests\PruebaRequest;
use Illuminate\Http\Request;
use App\Models\Prueba;
use Carbon\Carbon;

class PruebasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
		$data=Prueba::when(($request->input("searchString")!=""), function($q) use ($request){
			$q
			->orWhere("name", "like", "%".$request->searchString."%");
		})->
		when(($request->input("name")!=""), function($q) use ($request){
			$q->where("name", "like", "%".$request->name."%");
		})->paginate($perPage);

        return ApiResponseController::response('Consulta Exitosa', 200, $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PruebaRequest $request)
    {
        $prueba = new Prueba();

        $prueba->name = $request->name;
		

        $prueba->save();

        return ApiResponseController::response('Registro creado con exito', 200, $prueba);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prueba = Prueba::find($id);

        if(!$prueba){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        return ApiResponseController::response('Consulta Exitosa', 200, $prueba);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PruebaRequest $request, $id)
    {
        $prueba = Prueba::find($id);

        if(!$prueba){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        $prueba->name = $request->name;
		

        $prueba->save();

        return ApiResponseController::response('Registro actualizado con exito', 200, $prueba);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prueba = Prueba::find($id);

        if(!$prueba){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        $prueba->delete();
        return ApiResponseController::response('Registro eliminada con exito', 200, null);
    }


    public function getAll()
    {
        $data = Prueba::all();
        return ApiResponseController::response('Consulta Exitosa', 200, $data);
    }
}
