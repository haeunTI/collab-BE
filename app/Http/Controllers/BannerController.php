<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    /**
    *    @OA\Get(
    *       path="/banner",
    *       tags={"Banner"},
    *       operationId="banner",
    *       summary="ambil semua banner",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua banner",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all banner",
    *               "data": {
    *                   {
    *                   "id": 15,
    *                   "image": "1801733198090700.jpg",
    *                   "description": "bio",
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
            $banner = Banner::all(); 
            foreach ($banner as $item) {
                if ($item->image) {
                    try {
                        $item->image_url = GoogleDriveController::getImageUrl($item->image);
                    } catch (\Exception $e) {
                        $item->image_url = null; 
                        Log::error('Failed to fetch image URL for Banner ID ' . $item->id, ['error' => $e->getMessage()]);
                    }
                } else {
                    $item->image_url = null; 
                }
            }
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
    *    @OA\Post(
    *       path="/banner",
    *       tags={"Banner"},
    *       operationId="create_banner",
    *       summary="Create new banner",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new banner",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="image",
    *                       type="string",
    *                       format="binary",
    *                       description="The image of banner"
    *                   ),
    *                   @OA\Property(
    *                       property="description",
    *                       type="string",
    *                       description="The description of banner"
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
    *                   "message": "success post banner",
    *                   "data": {
    *                        "image": "1801799915486786.jpg",
    *                        "description": "lorem ipsum",
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
    *                   "message": "fail post banner",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
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
    *    @OA\Get(
    *       path="/banner/{id}",
    *       tags={"Banner"},
    *       operationId="single_banner",
    *       summary="Get single banner",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single banner by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the banner"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get blog",
    *                   "data": {
    *                       "id": 15,
    *                           "image": "1801556148382093.jpg",
    *                           "description": "ok",
    *                           "created_at": "2024-06-10T02:36:13.000000Z",
    *                           "updated_at": "2024-06-13T08:17:59.000000Z"    *                   }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="banner not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get banner",
    *                   "error": "No query results for model [App\\Models\\Banner] 10"
    *               }
    *           )
    *       )
    *    )
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
     *    @OA\Post(
     *       path="/banner/{id}",
     *       tags={"Banner"},
     *       operationId="update_banner",
     *       summary="Update banner",
     *       security={{"bearerAuth":{}}},
     *       description="Update banner by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of banner"
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(
     *                       property="description",
     *                       type="string",
     *                       description="The description of banner"
     *                   ),
     *                   @OA\Property(
     *                       property="image",
     *                       type="string",
     *                       format="binary",
     *                       description="The image of banner"
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
     *                   "message": "success update banner",
     *                   "data": {
     *                       "id": 1,
     *                       "description": "Updated banner",
     *                       "image": "updated_image_name.jpg",
     *                       "created_at": "2024-05-18 15:52:01",
     *                       "updated_at": "2024-05-18 15:52:01"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="banner not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update banner",
     *                   "error": "No query results for model [App\\Models\\Banner] 10"
     *               }
     *           )
     *       )
     *    )
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

            $banner->update([
                "description" => $req->description,
                "image" => $imageName,
                'updated_at' => Carbon::now()
            ]);

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
     *    @OA\Delete(
     *       path="/banner/{id}",
     *       tags={"Banner"},
     *       operationId="delete_banner",
     *       summary="Delete banner",
     *       security={{"bearerAuth":{}}},
     *       description="Delete a blog banner by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the banner"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete banner"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="banner not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete banner",
     *                   "error": "No query results for model [App\\Models\\Banner] 10"
     *               }
     *           )
     *       )
     *    )
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
