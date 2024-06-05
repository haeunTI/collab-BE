<?php

namespace App\Http\Controllers;

use App\Models\SocialMedia;
use App\Http\Requests\StoreSocialMediaRequest;
use App\Http\Requests\UpdateSocialMediaRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SocialMediaController extends Controller
{
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
     * Store a newly created resource in storage.
     */
    public function store(StoreSocialMediaRequest $req)
    {
        try{
            if($req->has('image')){
                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.social_media_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
                
                 if($response->successful()) {
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
                 } else {
                    return response([
                        "access" => $accessToken,
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                    ]);
                }
            } 
  
  
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
