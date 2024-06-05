<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use App\Http\Requests\StoreAboutUsRequest;
use App\Http\Requests\UpdateAboutUsRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AboutUsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function token() {
        $client_id = \Config('services.google.client_id');
        $client_secret = \Config('services.google.client_secret');
        $refresh_token = \Config('services.google.refresh_token');
        $response= Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $accessToken = json_decode((string)$response->getBody(), true)['access_token'];
        return $accessToken;

    }

    public function index()
    {
        try{
            $aboutUs = AboutUs::all(); 
            // $testing = $this->token();
 
            return response([
                "status" => true,
                "message" => "success get all about us",
                "data" => $aboutUs,
                "access" => $this->token()
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all about us",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAboutUsRequest $req)
    {
        try{
            if($req->has('image')){
              $manager = new ImageManager(new Driver());
              $img = $manager->read($req->file('image'));
              $img->resize(370, 370);
              $image = $req->image;
              $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
              $img->toJpeg()->save(public_path('img/about-us/' . $name_generator));
            } 
  
             $aboutUs = AboutUs::create([
                 "image" => $name_generator,
                 "description" => $req->description,
                 "created_at" => Carbon::now(),
             ]); 
  
             return response([
                 "status" => true,
                 "message" => "success post about us",
                 "data" => $aboutUs
             ]);
  
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post about us",
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
            $aboutUs = AboutUs::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single about us",
                "data" => $aboutUs
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all about us",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AboutUs $aboutUs)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAboutUsRequest $req, $id)
    {
        try {
            $aboutUs = AboutUs::findOrFail($id);

            if($req->has('image')){
                $manager = new ImageManager(new Driver());
                $img = $manager->read($req->file('image'));
                $img->resize(370, 370);
                $image = $req->image;
                $name_generator = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
                $img->toJpeg()->save(base_path('public/img/about-us/'.$name_generator));
            } 

            $aboutUs->update([
                'image' => $name_generator,
                'description' => $req->input('description'),
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update about us",
                "data" => $aboutUs
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update about us",
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
            AboutUs::findOrFail($id)->delete(); 
 
            return response([
                "status" => true,
                "message" => "success delete aboutUs",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete aboutUs",
                "error" => $th->getMessage()
            ]);
        }
    }
}
