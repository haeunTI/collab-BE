<?php

namespace App\Http\Controllers;

use App\Models\Testimony;
use App\Http\Requests\StoreTestimonyRequest;
use App\Http\Requests\UpdateTestimonyRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TestimonyController extends Controller
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
     * Store a newly created resource in storage.
     */
    public function store(StoreTestimonyRequest $req)
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
                        'parents' => [\Config('services.google.testimony_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
                
                if($response->successful()) {
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
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonyRequest $req, $id)
    {
        try {
            $testimony = Testimony::findOrFail($id);

            $name_generator = $testimony->image; 

            if($req->has('image')){
                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.testimony_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

                if (!$response->successful()) {
                    return response([
                        "status" => false,
                        "message" => "fail update testimony",
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                        "access" => $accessToken
                    ]);
                }

                if ($testimony->image) {
                    $this->deleteOldImageFromDrive($testimony->image, $accessToken);
                }
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
            $testimony = Testimony::findOrFail($id); 
            $imageName = $testimony->image;
 
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

            $testimony->delete();

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
