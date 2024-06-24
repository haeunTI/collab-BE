<?php

namespace App\Http\Controllers;

use App\Models\Testimony;
use App\Http\Requests\StoreTestimonyRequest;
use App\Http\Requests\UpdateTestimonyRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TestimonyController extends Controller
{
    /**
    *    @OA\Get(
    *       path="/testimony",
    *       tags={"Testimony"},
    *       operationId="testimony",
    *       summary="ambil semua Testimony",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua Testimony",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all testimony",
    *               "data": {
    *                   {
    *                   "id": 15,
    *                   "image": "1801550969236851.jpg",
    *                   "name" : "sip ok" ,
    *                   "business_name" : "ABC" ,
    *                   "description": "Sip",
    *                   "created_at": "2024-06-10T02:36:13.000000Z",
    *                   "updated_at": "2024-06-11T08:01:32.000000Z"
    *                  }
    *              }
    *          }),
    *      ),
    *  )
    */
    public function index()
    {
        try{
            $testimony = Testimony::all(); 
            foreach ($testimony as $item) {
                if ($item->image) {
                    try {
                        $item->image_url = GoogleDriveController::getImageUrl($item->image);
                    } catch (\Exception $e) {
                        $item->image_url = null; 
                        Log::error('Failed to fetch image URL for Testimony ID ' . $item->id, ['error' => $e->getMessage()]);
                    }
                } else {
                    $item->image_url = null; 
                }
            }
 
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
    *    @OA\Post(
    *       path="/testimony",
    *       tags={"Testimony"},
    *       operationId="create_testimony",
    *       summary="Create new Testimony",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new Testimony",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="business_name",
    *                       type="string",
    *                       description="The business_name of Testimony"
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
    *                   "message": "success post Testimony",
    *                   "data": {
    *                        "image": "1801797620165543.jpg",
    *                        "description": "lorem ipsumwfsjdkjfkjsdkfjakdjfk",
    *                         "name" : "sip ok" ,
    *                         "business_name" : "ABC" ,
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
    *                   "message": "fail post Testimony",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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
    *    @OA\Get(
    *       path="/testimony/{id}",
    *       tags={"Testimony"},
    *       operationId="single_testimony",
    *       summary="Get single Testimony",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single Testimony by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the Testimony"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get Testimony",
    *                   "data": {
    *                       "id": 15,
    *                           "image": "1801733198090700.jpg",
    *                           "description": "ok",
    *                           "name" : "sip ok" ,
    *                           "business_name" : "ABC" ,
    *                           "created_at": "2024-06-10T02:36:13.000000Z",
    *                           "updated_at": "2024-06-13T08:17:59.000000Z"    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="Testimony not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get Testimony",
    *                   "error": "No query results for model [App\\Models\\Testimony] 10"
    *               }
    *           )
    *       )
    *    )
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
     *    @OA\Post(
     *       path="/testimony/{id}",
     *       tags={"Testimony"},
     *       operationId="update_testimony",
     *       summary="Update Testimony",
     *       security={{"bearerAuth":{}}},
     *       description="Update Testimony by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of Testimony"
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
    *                       description="The image of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Testimony"
    *                   ),
    *                   @OA\Property(
    *                       property="business_name",
    *                       type="string",
    *                       description="The business_name of Testimony"
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
     *                   "message": "success update Testimony",
     *                   "data": {
     *                       "id": 1,
     *                       "description": "Updated Testimony",
     *                       "image": "updated_image_name.jpg",
     *                       "created_at": "2024-05-18 15:52:01",
     *                       "updated_at": "2024-05-18 15:52:01"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Testimony not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update Testimony",
     *                   "error": "No query results for model [App\\Models\\Testimony] 10"
     *               }
     *           )
     *       )
     *    )
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
     *    @OA\Delete(
     *       path="/testimony/{id}",
     *       tags={"Testimony"},
     *       operationId="delete_testimony",
     *       summary="Delete Testimony",
     *       security={{"bearerAuth":{}}},
     *       description="Delete Testimony by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the Testimony"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete Testimony"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Testimony not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete Testimony",
     *                   "error": "No query results for model [App\\Models\\Testimony] 10"
     *               }
     *           )
     *       )
     *    )
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
