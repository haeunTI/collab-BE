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
                $file = $req->file('image');
                $folderName = 'social_media'; 

                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);
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
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
            }
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post social media",
                 "error" => $th->getMessage()
             ]);
         }
    }

    public function show($id)
    {
        try{
            $socialMedia = SocialMedia::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single social Media",
                "data" => $socialMedia
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get single social Media",
                "error" => $th->getMessage()
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSocialMediaRequest $req, $id)
    {
        try {
            $socialMedia = SocialMedia::findOrFail($id);

            $folderName = 'social_media';
            $imageName = $socialMedia->image;

            if($req->has('image')){

                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if ($socialMedia->image) {
                    GoogleDriveController::deleteOldImageFromDrive($socialMedia->image);
                }

                $imageName = $newImageName;
            } 

            $socialMedia->update([
                "image" => $imageName,
                "name" => $req->name,
                "url" => $req->url,
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update social media",
                "data" => $socialMedia
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update social media",
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
            $socialMedia = SocialMedia::findOrFail($id); 
            $imageName = $socialMedia->image;
 
            if ($imageName) {
                GoogleDriveController::deleteOldImageFromDrive($imageName);
            }

            $socialMedia->delete();

            return response([
                "status" => true,
                "message" => "success delete social media",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete social media",
                "error" => $th->getMessage()
            ]);
        }
    }
}
