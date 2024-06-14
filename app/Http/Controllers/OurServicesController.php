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
    *    @OA\Get(
    *       path="/our-services",
    *       tags={"Our Services"},
    *       operationId="our_services",
    *       summary="ambil semua Our Services",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua Our Services",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all our_services",
    *               "data": {
    *                   {
    *                   "id": 15,
    *                   "image": "1801550969236851.jpg",
    *                   "title": "ok",
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
    *    @OA\Post(
    *       path="/our-services",
    *       tags={"Our Services"},
    *       operationId="create_our_services",
    *       summary="Create new Our Services",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new Our Services",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of Our Services"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Our Services"
    *                   ),
    *                   @OA\Property(
    *                       property="title",
    *                       type="string",
    *                       description="The title of Our Services"
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
    *                   "message": "success post Our Services",
    *                   "data": {
    *                        "id": 15,
    *                       "image": "1801550969236851.jpg",
    *                       "title": "ok",
    *                       "description": "Sip",
    *                       "created_at": "2024-06-10T02:36:13.000000Z",
    *                       "updated_at": "2024-06-11T08:01:32.000000Z"
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
    *                   "message": "fail post Our Services",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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
    *    @OA\Get(
    *       path="/our-services/{id}",
    *       tags={"Our Services"},
    *       operationId="single_our_services",
    *       summary="Get single Our Services",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single Our Services by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the Our Services"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get Our Services",
    *                   "data": {
    *                        "id": 15,
    *                       "image": "1801550969236851.jpg",
    *                       "title": "ok",
    *                       "description": "Sip",
    *                       "created_at": "2024-06-10T02:36:13.000000Z",
    *                       "updated_at": "2024-06-11T08:01:32.000000Z"
    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="Our Services not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get Our Services",
    *                   "error": "No query results for model [App\\Models\\OurServices] 10"
    *               }
    *           )
    *       )
    *    )
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
     *    @OA\Post(
     *       path="/our-services/{id}",
     *       tags={"Our Services"},
     *       operationId="update_our_services",
     *       summary="Update Our Services",
     *       security={{"bearerAuth":{}}},
     *       description="Update Our Services by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of Our Services"
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
    *                       description="The image of Our Services"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of Our Services"
    *                   ),
    *                   @OA\Property(
    *                       property="title",
    *                       type="string",
    *                       description="The title of Our Services"
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
     *                   "message": "success update Our Services",
     *                   "data": {
     *                       "id": 15,
    *                       "image": "1801550969236851.jpg",
    *                       "title": "ok",
    *                       "description": "Sip",
    *                       "created_at": "2024-06-10T02:36:13.000000Z",
    *                       "updated_at": "2024-06-11T08:01:32.000000Z"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Our Services not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update Our Services",
     *                   "error": "No query results for model [App\\Models\\OurServices] 10"
     *               }
     *           )
     *       )
     *    )
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

            $ourServices   ->update([
                "title" => $req->title,
                "description" => $req->description,
                "image" => $imageName,
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,
                "message" => "Our services updated successfully",
                "data" => $ourServices
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
     *    @OA\Delete(
     *       path="/our-service/{id}",
     *       tags={"Our Services"},
     *       operationId="delete_our_services",
     *       summary="Delete Our Services",
     *       security={{"bearerAuth":{}}},
     *       description="Delete Our Services by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the Our Services"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete Our Services"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Our Services not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete Our Services",
     *                   "error": "No query results for model [App\\Models\\OurServices] 10"
     *               }
     *           )
     *       )
     *    )
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
