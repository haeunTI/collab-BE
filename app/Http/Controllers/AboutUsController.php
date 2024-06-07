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

    public function index()
    {
        try{
            $aboutUs = AboutUs::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all about us",
                "data" => $aboutUs,
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
     * Store a newly created resource in storage.
     */
    public function store(StoreAboutUsRequest $req)
    {
        try{
            if($req->has('image')){

                $file = $req->file('image');
                $folderName = 'about_us'; 
                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);

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
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
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
     * Update the specified resource in storage.
     */
    public function update(UpdateAboutUsRequest $req, $id)
    {
        try {
            $aboutUs = AboutUs::findOrFail($id);
            $folderName = 'about_us';
            $imageName = $aboutUs->image;

            if ($req->hasFile('image')) {
                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if($aboutUs->image) {
                    GoogleDriveController::deleteOldImageFromDrive($aboutUs->image);

                }
                $imageName = $newImageName;
            }

            $aboutUs->image = $imageName;
            $aboutUs->save();

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
                    GoogleDriveController::deleteOldImageFromDrive($imageName);
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
