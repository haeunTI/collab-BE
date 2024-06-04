<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $banner = Banner::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all banner",
                "data" => $banner
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all banner",
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
    public function store(StoreBannerRequest $req)
    {
        try{
            if($req->has('image')){
              $manager = new ImageManager(new Driver());
              $img = $manager->read($req->file('image'));
              $img->resize(370, 370);
              $image = $req->image;
              $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
              $img->toJpeg()->save(base_path('public/img/banner/'.$name_generator));
            } 
  
             $banner = Banner::create([
                 "image" => $name_generator,
                 "description" => $req->description,
                 "created_at" => Carbon::now(),
             ]); 
  
             return response([
                 "status" => true,
                 "message" => "success post banner",
                 "data" => $banner
             ]);
  
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post banner",
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
            $banner = Banner::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single banner",
                "data" => $banner
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all banner",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $req, $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            if($req->has('image')){
                $manager = new ImageManager(new Driver());
                $img = $manager->read($req->file('image'));
                $img->resize(370, 370);
                $image = $req->image;
                $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
                $img->toJpeg()->save(base_path('public/img/banner/'.$name_generator));
            } 

            $banner->update([
                'image' => $name_generator,
                'description' => $req->input('description'),
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update banner",
                "data" => $banner
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update banner",
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
            Banner::findOrFail($id)->delete(); 
 
            return response([
                "status" => true,
                "message" => "success delete banner",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete banner",
                "error" => $th->getMessage()
            ]);
        }
    }
}
