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
                $file = $req->file('image');
                $folderName = 'testimony'; 
    
                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);
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
                        "status" => false,
                        "message" => "No image file found in the request",
                    ], 400);
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

            $folderName = 'testimony';
            $imageName = $testimony->image;

            if($req->has('image')){

                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if ($testimony->image) {
                    GoogleDriveController::deleteOldImageFromDrive($testimony->image);
                }

                $imageName = $newImageName;
            } 

            $testimony->update([
                "description" => $req->description,
                "image" => $imageName,
                "name" => $req->name,
                "business_name" => $req->business_name,
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
                GoogleDriveController::deleteOldImageFromDrive($imageName);
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
