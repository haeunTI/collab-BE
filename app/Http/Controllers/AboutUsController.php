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

    public function index()
    {
        try{
            $aboutUs = AboutUs::all(); 
 
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
                

                // $testing = Http::withHeaders([
                //     'Authorization' => 'Bearer '.$accessToken,
                //     'Content-Type' => 'Application/json',
                // ])->post('https://www.googleapis.com/drive/v3/files',[
                //         'data' => $name_generator,
                //         'mimeType' => $mimeType,
                //         'uploadType' => 'resumable',
                //         'parents' => [\Config('services.google.folder_id')]
                // ]
                // );

                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.about_us_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

                // Check the response
                
                 if($response->successful()) {
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

            $name_generator = $aboutUs->image; // Keep the current image name if no new image is uploaded

            if ($req->hasFile('image')) {
                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.folder_id')],
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

                if ($aboutUs->image) {
                    $this->deleteOldImageFromDrive($aboutUs->image, $accessToken);
                }
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
        try {
            $aboutUs = AboutUs::findOrFail($id);
            $imageName = $aboutUs->image;

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

            $aboutUs->delete();

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
