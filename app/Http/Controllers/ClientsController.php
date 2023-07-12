<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
		$data=Client::
        when(($request->input("dni")!=""), function($q) use ($request){
			$q->where("dni", "like", "%".$request->dni."%");
		})->
        when(($request->input("first_name")!=""), function($q) use ($request){
			$q->where("first_name", "like", "%".$request->first_name."%");
		})->
        when(($request->input("last_name")!=""), function($q) use ($request){
			$q->where("last_name", "like", "%".$request->last_name."%");
		})->
        when(($request->input("address")!=""), function($q) use ($request){
			$q->where("address", "like", "%".$request->address."%");
		})->
        when(($request->input("neighborhood")!=""), function($q) use ($request){
			$q->where("neighborhood", "like", "%".$request->neighborhood."%");
		})->
        when(($request->input("city")!=""), function($q) use ($request){
			$q->where("city", "like", "%".$request->city."%");
		})->
        when(($request->input("phone")!=""), function($q) use ($request){
			$q->where("phone", "like", "%".$request->phone."%");
		})->
        when(($request->input("campaign")!=""), function($q) use ($request){
			$q->where("campaign", "like", "%".$request->campaign."%");
		})->
        when(($request->input("zip_code")!=""), function($q) use ($request){
			$q->where("zip_code", "like", "%".$request->zip_code."%");
		})->
        when(($request->input("quota")!=""), function($q) use ($request){
			$q->where("quota", "like", "%".$request->quota."%");
		})->
        when(($request->input("balance")!=""), function($q) use ($request){
			$q->where("balance", "like", "%".$request->balance."%");
		})->
        when(($request->input("available_points")!=""), function($q) use ($request){
			$q->where("available_points", "like", "%".$request->available_points."%");
		})->
        when(($request->input("client_code")!=""), function($q) use ($request){
			$q->where("client_code", "like", "%".$request->client_code."%");
		})->limit(20)->get();

        return ApiResponseController::response('Consulta Exitosa', 200, $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client = Client::
        where("id", $id)
		/* Add new relationships here */




        ->first();

        return ApiResponseController::response('Consulta Exitosa', 200, $client);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
