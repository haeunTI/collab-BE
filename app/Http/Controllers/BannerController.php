<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BannerController extends Controller
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

    private function deleteOldImageFromDrive($filename, $accessToken) {
        $fileIdResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://www.googleapis.com/drive/v3/files', [
            'q' => "name='$filename' and trashed=false",
            'fields' => 'files(id, name)',
        ]);

        if ($fileIdResponse->successful()) {
            $files = json_decode($fileIdResponse->body(), true)['files'];
            if (!empty($files)) {
                $fileId = $files[0]['id'];

                $deleteResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->delete("https://www.googleapis.com/drive/v3/files/$fileId");

                return $deleteResponse->successful();
            }
        }

        return false;
    }
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
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $req)
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
                        'parents' => [\Config('services.google.banner_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
                
                 if($response->successful()) {
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
                 }  else {
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

            $name_generator = $banner->image;

            if($req->hasFile('image')){
                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.banner_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

                if (!$response->successful()) {
                    return response([
                        "status" => false,
                        "message" => "fail update about us",
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                        "access" => $accessToken
                    ]);
                }

                if ($banner->image) {
                    $this->deleteOldImageFromDrive($banner->image, $accessToken);
                }
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
        try {
            // Find the banner first
            $banner = Banner::findOrFail($id); 
            $imageName = $banner->image;

            if ($imageName) {
                $accessToken = $this->token();

                $fileIdResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->get('https://www.googleapis.com/drive/v3/files', [
                    'q' => "name='$imageName' and trashed=false",
                    'fields' => 'files(id, name)',
                ]);

                if ($fileIdResponse->successful()) {
                    $files = json_decode($fileIdResponse->body(), true)['files'];
                    if (!empty($files)) {
                        $fileId = $files[0]['id'];

                        $deleteResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $accessToken,
                        ])->delete("https://www.googleapis.com/drive/v3/files/$fileId");

                        if (!$deleteResponse->successful()) {
                            return response([
                                "status" => false,
                                "message" => "fail delete image from Google Drive",
                                "response_body" => $deleteResponse->body(),
                                "response_status" => $deleteResponse->status(),
                            ]);
                        }
                    }
                } else {
                    return response([
                        "status" => false,
                        "message" => "fail fetch file ID from Google Drive",
                        "response_body" => $fileIdResponse->body(),
                        "response_status" => $fileIdResponse->status(),
                    ]);
                }
            }

            // Delete the banner after processing the image
            $banner->delete();

            return response([
                "status" => true,
                "message" => "Banner and associated image deleted successfully",
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
