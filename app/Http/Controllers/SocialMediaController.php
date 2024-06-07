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

}
