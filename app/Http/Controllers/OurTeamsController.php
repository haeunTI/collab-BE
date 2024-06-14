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
    *    @OA\Get(
    *       path="/our-teams",
    *       tags={"Our Team"},
    *       operationId="our_teams",
    *       summary="ambil semua Our Team",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua Our Team",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all our_teams",
    *               "data": {
    *                   {
    *                   "id": 8,
    *                   "image": "1801532517550099.png",
    *                   "name": "sip",
    *                   "description": "ok",
    *                   "created_at": "2024-06-11T03:08:13.000000Z",
    *                   "updated_at": "2024-06-11T03:08:13.000000Z"
    *                  }
    *              }
    *          }),
    *      ),
    *  )
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
    *    @OA\Post(
    *       path="/our-teams",
    *       tags={"Our Teams"},
    *       operationId="create_our_teams",
    *       summary="Create new Our Teams",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new Our Teams",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of Our Teams"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Our Teams"
    *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Our Teams"
    *                   )
    *               )
    *           )
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success post Our Teams",
    *                   "data": {
    *                        "image": "1801797620165543.jpg",
    *                        "name": "ok",
    *                        "description": "lorem ipsumwfsjdkjfkjsdkfjakdjfk",
    *                        "created_at": "2024-06-14T01:21:56.000000Z",
    *                        "updated_at": "2024-06-14T01:21:56.000000Z",
    *                        "id": 21
    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="400",
    *           description="Bad request",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail post Our Teams",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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
    *    @OA\Get(
    *       path="/our-teams/{id}",
    *       tags={"Our Teams"},
    *       operationId="single_our_teams",
    *       summary="Get single Our Teams",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single Our Teams by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the Our Teams"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get Our Teams",
    *                   "data": {
    *                       "id": 8,
    *                   "image": "1801532517550099.png",
    *                   "name": "sip",
    *                   "description": "ok",
    *                   "created_at": "2024-06-11T03:08:13.000000Z",
    *                   "updated_at": "2024-06-11T03:08:13.000000Z"
    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="Our Teams not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get Our Teams",
    *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
    *               }
    *           )
    *       )
    *    )
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
     *    @OA\Post(
     *       path="/our-teams/{id}",
     *       tags={"Our Teams"},
     *       operationId="update_our_teams",
     *       summary="Update Our Teams",
     *       security={{"bearerAuth":{}}},
     *       description="Update Our Teams by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of Our Teams"
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of Our Teams"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Our Teams"
    *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Our Teams"
    *                   )
     *               )
     *           )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success update Our Teams",
     *                   "data": {
     *                       "id": 1,
     *                       "description": "Updated Our Teams",
     *                       "image": "updated_image_name.jpg",
     *                       "created_at": "2024-05-18 15:52:01",
     *                       "updated_at": "2024-05-18 15:52:01"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Our Teams not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update Our Teams",
     *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
     *               }
     *           )
     *       )
     *    )
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

            $ourTeams   ->update([
                "name" => $req->name,
                "description" => $req->description,
                "image" => $imageName,
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
     *    @OA\Delete(
     *       path="/our-teams/{id}",
     *       tags={"Our Teams"},
     *       operationId="delete_our_teams",
     *       summary="Delete Our Teams",
     *       security={{"bearerAuth":{}}},
     *       description="Delete Our Teams by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the Our Teams"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete Our Teams"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Our Teams not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete Our Teams",
     *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
     *               }
     *           )
     *       )
     *    )
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
