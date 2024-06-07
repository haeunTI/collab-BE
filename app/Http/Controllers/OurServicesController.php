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
                $folderName = 'our_services'; 
    
                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);
    

                    $ourServices = OurServices::create([
                        "title" => $req->title,
                        "image" => $name_generator,
                        "description" => $req->description,
                        "created_at" => Carbon::now(),
                    ]);
    
                    return response([
                        "status" => true,
                        "message" => "success post our services",
                        "data" => $ourServices
                    ]);

            } else {
                return response([
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
            }
        } catch (\Throwable $th) {    
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
            $folderName = 'our_services';
            $imageName = $ourServices->image;

            if ($req->has('image')) {
                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if ($ourServices->image) {
                    GoogleDriveController::deleteOldImageFromDrive($ourServices->image);
                }

                $imageName = $newImageName;
            }

            $ourServices->image = $imageName;
            $ourServices->save();

            return response([
                "status" => true,
                "message" => "Our services updated successfully",
            ]);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => "Failed to update our services",
                "error" => $e->getMessage(),
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
