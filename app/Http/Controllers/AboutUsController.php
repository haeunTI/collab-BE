<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use App\Http\Requests\StoreAboutUsRequest;
use App\Http\Requests\UpdateAboutUsRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AboutUsController extends Controller
{

    /**
    *    @OA\Get(
    *       path="/about-us",
    *       tags={"About Us"},
    *       operationId="about_us",
    *       summary="ambil semua about us",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua about us",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all about_us",
    *               "data": {
    *                   {
    *                   "id": 15,
    *                   "image": "1801550969236851.jpg",
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
            $aboutUs = AboutUs::all(); 
 
            foreach ($aboutUs as $item) {
                if ($item->image) {
                    try {
                        $item->image_url = GoogleDriveController::getImageUrl($item->image);
                    } catch (\Exception $e) {
                        $item->image_url = null;
                        Log::error('Failed to fetch image URL for AboutUs ID ' . $item->id, ['error' => $e->getMessage()]);
                    }
                } else {
                    $item->image_url = null; 
                }
            }
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
    *    @OA\Post(
    *       path="/about-us",
    *       tags={"About Us"},
    *       operationId="create_about_us",
    *       summary="Create new about us",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new about us",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of about us"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of about us"
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
    *                   "message": "success post about us",
    *                   "data": {
    *                        "image": "1801797620165543.jpg",
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
    *                   "message": "fail post about us",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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
    *    @OA\Get(
    *       path="/about-us/{id}",
    *       tags={"About Us"},
    *       operationId="single_about_us",
    *       summary="Get single about us",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single about us by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the about us"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get about us",
    *                   "data": {
    *                       "id": 15,
    *                           "image": "1801733198090700.jpg",
    *                           "description": "ok",
    *                           "created_at": "2024-06-10T02:36:13.000000Z",
    *                           "updated_at": "2024-06-13T08:17:59.000000Z"    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="about us not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get about us",
    *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
    *               }
    *           )
    *       )
    *    )
    */
    public function show($id)
    {
        try{
            $aboutUs = AboutUs::findOrFail($id); 

            if ($aboutUs->image) {
                try {
                    $aboutUs->image_url = GoogleDriveController::getImageUrl($aboutUs->image);
                } catch (\Exception $e) {
                    $aboutUs->image_url = null; // Set image_url to null if fetching fails
                    Log::error('Failed to fetch image URL for AboutUs ID ' . $aboutUs->id, ['error' => $e->getMessage()]);
                }
            } else {
                $aboutUs->image_url = null; // Set image_url to null if image is not set
            }
     
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
     *    @OA\Post(
     *       path="/about-us/{id}",
     *       tags={"About Us"},
     *       operationId="update_about_us",
     *       summary="Update about us",
     *       security={{"bearerAuth":{}}},
     *       description="Update about us by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of about us"
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(
     *                       property="description",
     *                       type="string",
     *                       description="The description of about us"
     *                   ),
     *                   @OA\Property(
     *                       property="image",
     *                       type="string",
     *                       format="binary",
     *                       description="The image of about us"
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
     *                   "message": "success update about us",
     *                   "data": {
     *                       "id": 1,
     *                       "description": "Updated About Us",
     *                       "image": "updated_image_name.jpg",
     *                       "created_at": "2024-05-18 15:52:01",
     *                       "updated_at": "2024-05-18 15:52:01"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="About us not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update about us",
     *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
     *               }
     *           )
     *       )
     *    )
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

            $aboutUs->update([
                "description" => $req->description,
                "image" => $imageName,
                'updated_at' => Carbon::now()
            ]);

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
     *    @OA\Delete(
     *       path="/about-us/{id}",
     *       tags={"About Us"},
     *       operationId="delete_about_us",
     *       summary="Delete about us",
     *       security={{"bearerAuth":{}}},
     *       description="Delete about us by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the about us"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete about us"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="About us not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete about us",
     *                   "error": "No query results for model [App\\Models\\AboutUs] 10"
     *               }
     *           )
     *       )
     *    )
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
