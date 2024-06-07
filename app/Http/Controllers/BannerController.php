<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BannerController extends Controller
{
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
                $file = $req->file('image');
                $folderName = 'banner'; 

                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);
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
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
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
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $req, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $folderName = 'banner';
            $imageName = $banner->image;

            if($req->hasFile('image')){
                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if ($banner->image) {
                    GoogleDriveController::deleteOldImageFromDrive($banner->image);
                }

                $imageName = $newImageName;
            } 

            $banner->image = $imageName;
            $banner->save();

            return response([
                "status" => true,
                "message" => "Our services updated successfully",
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
            $banner = Banner::findOrFail($id); 
            $imageName = $banner->image;

            if ($imageName) {
                GoogleDriveController::deleteOldImageFromDrive($imageName);
            }

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
