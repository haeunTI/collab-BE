<?php

namespace App\Http\Controllers;

use App\Models\OurServices;
use App\Http\Requests\StoreOurServicesRequest;
use App\Http\Requests\UpdateOurServicesRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OurServicesController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $ourServices = OurServices::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all testimonies",
                "data" => $ourServices
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
    public function store(StoreOurServicesRequest $req)
    {
        try {
            if ($req->has('image')) {
                $file = $req->file('image');
                $name_generator = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
                $folderName = 'our_services'; // Define your folder name here
    
                // Check and create folder if it doesn't exist
                $folderId = GoogleDriveController::checkAndCreateFolder($folderName);
    
                // Upload the file to the folder
                $accessToken = GoogleDriveController::getAccessToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [$folderId],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
    
                if ($response->successful()) {
                    $ourServices = OurServices::create([
                        "title" => $req->title,
                        "image" => $name_generator,
                        "description" => $req->description,
                        "created_at" => Carbon::now(),
                    ]);
    
                    return response([
                        "status" => true,
                        "message" => "success post our services",
                        "data" => $folderId
                    ]);
                } else {
                    Log::error('File upload failed', [
                        'access' => $accessToken,
                        'response_body' => $response->body(),
                        'response_status' => $response->status(),
                    ]);
    
                    return response([
                        "status" => false,
                        "message" => "Failed to upload file to Google Drive",
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                    ], 500);
                }
            } else {
                return response([
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
            }
        } catch (\Throwable $th) {
            Log::error('Failed to post our services', ['error' => $th->getMessage()]);
    
            return response([
                "status" => false,
                "message" => "fail post our services",
                "error" => $th->getMessage()
            ], 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $ourServices = OurServices::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single our services",
                "data" => $ourServices
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get our services",
                "error" => $th->getMessage()
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOurServicesRequest $req, $id)
    {
        try {
            $ourServices = OurServices::findOrFail($id);
            $name_generator = $ourServices->image;
    
            if ($req->has('image')) {
                $file = $req->file('image');
                $name_generator = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();
                $folderName = 'our_services'; 
    
                $folderId = GoogleDriveController::checkAndCreateFolder($folderName);
    
                $accessToken = GoogleDriveController::getAccessToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->attach(
                    'metadata', json_encode([
                        'name' => $name_generator,
                        'parents' => [$folderId],
                    ]), 'metadata.json'
                )->attach(
                    'file', fopen($file->getPathname(), 'r'), $name_generator
                )->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
    
                if (!$response->successful()) {
                    return response([
                        "status" => false,
                        "message" => "fail update our services",
                        "response_body" => $response->body(),
                        "response_status" => $response->status(),
                        "access" => $accessToken
                    ]);
                }
    
                if ($ourServices->image) {
                    GoogleDriveController::deleteOldImageFromDrive($ourServices->image);
                }
            }
    
            $ourServices->update([
                "title" => $req->name,
                "image" => $name_generator,
                "description" => $req->description,
                'updated_at' => Carbon::now()
            ]);
    
            return response([
                "status" => true,
                "message" => "success update our services",
                "data" => $ourServices
            ]);
    
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update our services",
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
            $ourServices = OurServices::findOrFail($id);
            $imageName = $ourServices->image;
    
            if ($imageName) {
                GoogleDriveController::deleteOldImageFromDrive($imageName);
            }
    
            $ourServices->delete();
    
            return response([
                "status" => true,
                "message" => "success delete our services",
            ]);
    
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete our services",
                "error" => $th->getMessage()
            ]);
        }
    }
}
