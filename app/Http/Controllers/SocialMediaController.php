<?php

namespace App\Http\Controllers;

use App\Models\SocialMedia;
use App\Http\Requests\StoreSocialMediaRequest;
use App\Http\Requests\UpdateSocialMediaRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SocialMediaController extends Controller
{

    /**
    *    @OA\Get(
    *       path="/social-media",
    *       tags={"Social Media"},
    *       operationId="social_media",
    *       summary="ambil semua Social Media",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua Social Media",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all social_media",
    *               "data": {
    *                   {
    *                   "id": 15,
    *                   "image": "1801550969236851.jpg",
    *                   "name": "Sip",
    *                   "url": "Sip",
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
            $socialMedia = SocialMedia::all(); 
            foreach ($socialMedia as $item) {
                if ($item->image) {
                    try {
                        $item->image_url = GoogleDriveController::getImageUrl($item->image);
                    } catch (\Exception $e) {
                        $item->image_url = null; 
                        Log::error('Failed to fetch image URL for Social Media ID ' . $item->id, ['error' => $e->getMessage()]);
                    }
                } else {
                    $item->image_url = null; 
                }
            }
 
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
    *    @OA\Post(
    *       path="/social-media",
    *       tags={"Social Media"},
    *       operationId="create_social_media",
    *       summary="Create new Social Media",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new Social Media",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of Social Media"
    *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Social Media"
    *                   ),
    *                   @OA\Property(
    *                       property="url",
    *                       type="string",
    *                       description="The url of Social Media"
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
    *                   "message": "success post Social Media",
    *                   "data": {
    *                       "image": "1801550969236851.jpg",
    *                       "name": "Sip",
    *                       "url": "Sip",
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
    *                   "message": "fail post Social Media",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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

    /**
    *    @OA\Get(
    *       path="/social-media/{id}",
    *       tags={"Social Media"},
    *       operationId="single_social_media",
    *       summary="Get single Social Media",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single Social Media by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the Social Media"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get Social Media",
    *                   "data": {
    *                       "id": 15,
    *                           "image": "1801733198090700.jpg",
    *                           "name": "Sip",
    *                           "url": "Sip",
    *                           "created_at": "2024-06-10T02:36:13.000000Z",
    *                           "updated_at": "2024-06-13T08:17:59.000000Z"    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="Social Media not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get Social Media",
    *                   "error": "No query results for model [App\\Models\\SocialMedia] 10"
    *               }
    *           )
    *       )
    *    )
    */
    public function show($id)
    {
        try{
            $socialMedia = SocialMedia::findOrFail($id); 

            if ($socialMedia->image) {
                try {
                    $socialMedia->image_url = GoogleDriveController::getImageUrl($socialMedia->image);
                } catch (\Exception $e) {
                    $socialMedia->image_url = null; // Set image_url to null if fetching fails
                    Log::error('Failed to fetch image URL for social$socialMedia ID ' . $socialMedia->id, ['error' => $e->getMessage()]);
                }
            } else {
                $socialMedia->image_url = null; // Set image_url to null if image is not set
            }
 
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
     *    @OA\Post(
     *       path="/social-media/{id}",
     *       tags={"Social Media"},
     *       operationId="update_social_media",
     *       summary="Update Social Media",
     *       security={{"bearerAuth":{}}},
     *       description="Update Social Media by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of Social Media"
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
     *                       description="The image of Social Media"
     *                   ),
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of Social Media"
    *                   ),
    *                   @OA\Property(
    *                       property="url",
    *                       type="string",
    *                       description="The url of Social Media"
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
     *                   "message": "success update Social Media",
     *                   "data": {
     *                       "id": 1,
     *                       "name": "Twitter",
     *                       "url" : "www.twitter.com",
     *                       "image": "updated_image_name.jpg",
     *                       "created_at": "2024-05-18 15:52:01",
     *                       "updated_at": "2024-05-18 15:52:01"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Social Media not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update Social Media",
     *                   "error": "No query results for model [App\\Models\\SocialMedia] 10"
     *               }
     *           )
     *       )
     *    )
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
     *    @OA\Delete(
     *       path="/social-media/{id}",
     *       tags={"Social Media"},
     *       operationId="delete_social_media",
     *       summary="Delete Social Media",
     *       security={{"bearerAuth":{}}},
     *       description="Delete Social Media by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the Social Media"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete Social Media"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="Social Media not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete Social Media",
     *                   "error": "No query results for model [App\\Models\\SocialMedia] 10"
     *               }
     *           )
     *       )
     *    )
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
