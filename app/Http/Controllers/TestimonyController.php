<?php

namespace App\Http\Controllers;

use App\Models\Testimony;
use App\Http\Requests\StoreTestimonyRequest;
use App\Http\Requests\UpdateTestimonyRequest;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TestimonyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $testimony = Testimony::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all testimonies",
                "data" => $testimony
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all testimonies",
                "error" => $th->getMessage()
            ]);
        }
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
    public function store(StoreTestimonyRequest $req)
    {
        try{
            if($req->has('image')){
              $manager = new ImageManager(new Driver());
              $img = $manager->read($req->file('image'));
              $img->resize(370, 370);
              $image = $req->image;
              $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
              $img->toJpeg()->save(base_path('public/img/testimony/'.$name_generator));
            } 
  
             $testimony = Testimony::create([
                "name" => $req->name,
                "image" => $name_generator,
                "business_name" => $req->business_name,
                "description" => $req->description,
                "created_at" => Carbon::now(),
             ]); 
  
             return response([
                 "status" => true,
                 "message" => "success post testimony",
                 "data" => $testimony
             ]);
  
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post testimony",
                 "error" => $th->getMessage()
             ]);
         }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $testimony = Testimony::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single testimony",
                "data" => $testimony
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all testimony",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimony $testimony)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonyRequest $req, $id)
    {
        try {
            $testimony = Testimony::findOrFail($id);

            if($req->has('image')){
                $manager = new ImageManager(new Driver());
                $img = $manager->read($req->file('image'));
                $img->resize(370, 370);
                $image = $req->image;
                $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
                $img->toJpeg()->save(base_path('public/img/testimony/'.$name_generator));
            } 


            $testimony->update([
                "name" => $req->name,
                "business_name" => $req->business_name,
                "image" => $name_generator,
                "description" => $req->description,
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update testimony",
                "data" => $testimony
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update testimony",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            Testimony::findOrFail($id)->delete(); 
 
            return response([
                "status" => true,
                "message" => "success delete testimony",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete testimony",
                "error" => $th->getMessage()
            ]);
        }
    }
}
