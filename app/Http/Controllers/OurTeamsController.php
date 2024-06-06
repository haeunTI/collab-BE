<?php

namespace App\Http\Controllers;

use App\Models\OurTeams;
use App\Http\Requests\StoreOurTeamsRequest;
use App\Http\Requests\UpdateOurTeamsRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class OurTeamsController extends Controller
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
            $ourTeams = OurTeams::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all our teams",
                "data" => $ourTeams
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all our teams",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOurTeamsRequest $req)
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
                        'parents' => [\Config('services.google.our_teams_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
                
                if($response->successful()) {
                    $ourTeams = OurTeams::create([
                        "name" => $req->name,
                        "image" => $name_generator,
                        "description" => $req->description,
                        "created_at" => Carbon::now(),
                     ]); 

                    return response([
                        "status" => true,
                        "message" => "success post our teams",
                        "data" => $ourTeams
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
                 "message" => "fail post our teams",
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
            $ourTeams = OurTeams::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single our teams",
                "data" => $ourTeams
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all our teams",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOurTeamsRequest $req, $id)
    {
        try {
            $ourTeams = OurTeams::findOrFail($id);

            $name_generator = $ourTeams->image; 

            if($req->has('image')){
                $accessToken = $this->token();

                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [\Config('services.google.our_teams_folder_id')],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

                if (!$response->successful()) {
                    return response([
                        "status" => false,
                        "message" => "fail update our teams",
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                        "access" => $accessToken
                    ]);
                }

                if ($ourTeams->image) {
                    $this->deleteOldImageFromDrive($ourTeams->image, $accessToken);
                }
            } 


            $ourTeams->update([
                "name" => $req->name,
                "business_name" => $req->business_name,
                "image" => $name_generator,
                "description" => $req->description,
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update our teams",
                "data" => $ourTeams
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update our teams",
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
            $ourTeams = OurTeams::findOrFail($id); 
            $imageName = $ourTeams->image;
 
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

            $ourTeams->delete();

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
