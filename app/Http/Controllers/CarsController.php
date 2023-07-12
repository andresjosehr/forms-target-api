<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use Illuminate\Http\Request;
use App\Models\Car;
use Carbon\Carbon;

class CarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
		$data=Car::when(($request->input("searchString")!=""), function($q) use ($request){
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
    public function store(CarRequest $request)
    {
        $car = new Car();

        $car->name = $request->name;
		

        $car->save();

        return ApiResponseController::response('Registro creado con exito', 200, $car);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $car = Car::find($id);

        if(!$car){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        return ApiResponseController::response('Consulta Exitosa', 200, $car);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CarRequest $request, $id)
    {
        $car = Car::find($id);

        if(!$car){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        $car->name = $request->name;
		

        $car->save();

        return ApiResponseController::response('Registro actualizado con exito', 200, $car);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $car = Car::find($id);

        if(!$car){
            return ApiResponseController::response('No se encontro el registro', 204, null);
        }

        $car->delete();
        return ApiResponseController::response('Registro eliminada con exito', 200, null);
    }


    public function getAll()
    {
        $data = Car::all();
        return ApiResponseController::response('Consulta Exitosa', 200, $data);
    }
}
