<?php

namespace App\Http\Controllers;

use App\Models\OurTeams;
use App\Http\Requests\StoreOurTeamsRequest;
use App\Http\Requests\UpdateOurTeamsRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class OurTeamsController extends Controller
{
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
                $file = $req->file('image');
                $folderName = 'our_teams'; 

                $name_generator = GoogleDriveController::uploadImageToFolder($file, $folderName);
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
                    "status" => false,
                    "message" => "No image file found in the request",
                ], 400);
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
            $folderName = 'our_teams';
            $imageName = $ourTeams->image;

            if($req->has('image')){
                $file = $req->file('image');
                $newImageName = GoogleDriveController::uploadImageToFolder($file, $folderName);

                if ($ourTeams->image) {
                    GoogleDriveController::deleteOldImageFromDrive($ourTeams->image);
                }

                $imageName = $newImageName;
            } 

            $ourTeams->image = $imageName;
            $ourTeams->save();

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
                GoogleDriveController::deleteOldImageFromDrive($imageName);     
            }

            $ourTeams->delete();

            return response([
                "status" => true,
                "message" => "success delete our teams",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete our teams",
                "error" => $th->getMessage()
            ]);
        }
    }
}
