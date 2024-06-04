<?php

namespace App\Http\Controllers;

use App\Models\SocialMedia;
use App\Http\Requests\StoreSocialMediaRequest;
use App\Http\Requests\UpdateSocialMediaRequest;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SocialMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $socialMedia = SocialMedia::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all social media",
                "data" => $socialMedia
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all social media",
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
    public function store(StoreSocialMediaRequest $req)
    {
        try{
            if($req->has('image')){
              $manager = new ImageManager(new Driver());
              $img = $manager->read($req->file('image'));
              $img->resize(370, 370);
              $image = $req->image;
              $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
              $img->toJpeg()->save(base_path('public/img/social-media/'.$name_generator));
            } 
  
             $socialMedia = SocialMedia::create([
                "name" => $req->name,
                "image" => $name_generator,
                "url" => $req->url,
                "created_at" => Carbon::now(),
             ]); 
  
             return response([
                 "status" => true,
                 "message" => "success post social media",
                 "data" => $socialMedia
             ]);
  
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post social media",
                 "error" => $th->getMessage()
             ]);
         }
    }

    /**
     * Display the specified resource.
     */
    public function show(SocialMedia $socialMedia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SocialMedia $socialMedia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSocialMediaRequest $request, SocialMedia $socialMedia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SocialMedia $socialMedia)
    {
        //
    }
}
