<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Http\Requests\StoreContactUsRequest;
use App\Http\Requests\UpdateContactUsRequest;
use Carbon\Carbon;

class ContactUsController extends Controller
{
    /**
    *    @OA\Get(
    *       path="/contact-us",
    *       tags={"Contact Us"},
    *       operationId="contact_us",
    *       summary="ambil semua contact us",
    *       security={{"bearerAuth":{}}},
    *       description="Mengambil Data Semua contact us",
    *       @OA\Response(
    *           response="200",
    *           description="Ok",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "success get all contact_us",
    *               "data": {
    *                   {
    *                   "id": 5,
    *                   "name": "ja3hyun okkk",
    *                   "email": "leepresent@tes.com",
    *                   "phone": "081231231",
    *                   "address": "qt",
    *                   "message": "watch it dabom okk",
    *                   "created_at": "2024-06-07T05:00:21.000000Z",
    *                   "updated_at": "2024-06-11T08:20:45.000000Z"
    *                  }
    *              }
    *          }),
    *      ),
    *  )
    */
    public function index()
    {
        try{
            $contactUs = ContactUs::all(); 
 
            return response([
                "status" => true,
                "message" => "success get all contact us",
                "data" => $contactUs
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all contact us",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
    *    @OA\Post(
    *       path="/contact-us",
    *       tags={"Contact Us"},
    *       operationId="create_contact_us",
    *       summary="Create new contact us",
    *       security={{"bearerAuth":{}}},
    *       description="Create a new contact us",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   @OA\Property(
    *                       property="name",
    *                       type="string",
    *                       description="The name of contact us"
    *                   ),
    *                   @OA\Property(
    *                       property="email",
    *                       type="string",
    *                       description="The email of contact us"
    *                   ),
    *                   @OA\Property(
    *                       property="phone",
    *                       type="string",
    *                       description="The phone of contact us"
    *                   ),
    *                   @OA\Property(
    *                       property="address",
    *                       type="string",
    *                       description="The address of contact us"
    *                   ),
    *                   @OA\Property(
    *                       property="message",
    *                       type="string",
    *                       description="The messae of contact us"
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
    *                   "message": "success post contact us",
    *                   "data": {
    *                        "name": "tbzuyeon",
    *                        "email": "tbzuyeonie@tes.com",
    *                        "phone": "081231231",
    *                        "address": "lorem ipsumieeez",
    *                        "message": "watch it dabom",
    *                        "created_at": "2024-06-14T02:31:17.000000Z",
    *                        "updated_at": "2024-06-14T02:31:17.000000Z",
    *                        "id": 9
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
    *                   "message": "fail post contact us",
    *                   "error": "Validation error message"
    *               }
    *           )
    *       )
    *    )
    */
    public function store(StoreContactUsRequest $req)
    {
        try{
             $blog = ContactUs::create([
                 "name" => $req->name,
                 "email" => $req->email,
                 "phone" => $req->phone,
                 "address" => $req->address,
                 "message" => $req->message,
                 "created_at" => Carbon::now(),
             ]); 
  
             return response([
                 "status" => true,
                 "message" => "success post contact us",
                 "data" => $blog
             ]);
  
         } catch (\Throwable $th) {
             return response([
                 "status" => false,
                 "message" => "fail post contact us",
                 "error" => $th->getMessage()
             ]);
         }
    }

    /**
    *    @OA\Get(
    *       path="/contact-us/{id}",
    *       tags={"Contact Us"},
    *       operationId="single_contact_us",
    *       summary="Get single contact us",
    *       security={{"bearerAuth":{}}},
    *       description="Retrieve a single contact us by ID",
    *       @OA\Parameter(
    *           name="id",
    *           in="path",
    *           required=true,
    *           @OA\Schema(type="integer"),
    *           description="The ID of the contact us"
    *       ),
    *       @OA\Response(
    *           response="200",
    *           description="Successful response",
    *           @OA\JsonContent(
    *               example={
    *                   "status": true,
    *                   "message": "success get contact us",
    *                   "data": {
    *                       "id": 5,
    *                       "name": "ja3hyun okkk",
    *                       "email": "leepresent@tes.com",
    *                       "phone": "081231231",
    *                       "address": "qt",
    *                       "message": "watch it dabom okk",
    *                       "created_at": "2024-06-07T05:00:21.000000Z",
    *                       "updated_at": "2024-06-11T08:20:45.000000Z"
    *                  }
    *               }
    *           )
    *       ),
    *       @OA\Response(
    *           response="404",
    *           description="contact us not found",
    *           @OA\JsonContent(
    *               example={
    *                   "status": false,
    *                   "message": "fail get contact us",
    *                   "error": "No query results for model [App\\Models\\contactUs] 10"
    *               }
    *           )
    *       )
    *    )
    */
    public function show($id)
    {
        try{
            $contactUs = ContactUs::findOrFail($id); 
 
            return response([
                "status" => true,
                "message" => "success get single contact us",
                "data" => $contactUs
            ]);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail get all contact us",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     *    @OA\Post(
     *       path="/contact-us/{id}",
     *       tags={"Contact Us"},
     *       operationId="update_contact_us",
     *       summary="Update contact us",
     *       security={{"bearerAuth":{}}},
     *       description="Update contact us by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of contact us"
     *       ),
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(
     *                       property="description",
     *                       type="string",
     *                       description="The description of contact us"
     *                   ),
     *                   @OA\Property(
     *                       property="image",
     *                       type="string",
     *                       format="binary",
     *                       description="The image of contact us"
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
     *                   "message": "success update contact us",
     *                   "data": {
     *                       "id": 5,
    *                       "name": "ja3hyun okkk",
    *                       "email": "leepresent@tes.com",
    *                       "phone": "081231231",
    *                       "address": "qt",
    *                       "message": "watch it dabom okk",
    *                       "created_at": "2024-06-07T05:00:21.000000Z",
    *                       "updated_at": "2024-06-11T08:20:45.000000Z"
     *                   }
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="contact us not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail update contact us",
     *                   "error": "No query results for model [App\\Models\\contactUs] 10"
     *               }
     *           )
     *       )
     *    )
     */
    public function update(UpdateContactUsRequest $req, $id)
    {
        try {
            $contactUs = ContactUs::findOrFail($id);

            $contactUs->update([
                "name" => $req->name,
                "email" => $req->email,
                "phone" => $req->phone,
                "address" => $req->address,
                "message" => $req->message,
                'updated_at' => Carbon::now()
            ]);

            return response([
                "status" => true,  
                "message" => "success update contact us",
                "data" => $contactUs
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail update contact us",
                "error" => $th->getMessage()
            ]);
        }
    }

    /**
     *    @OA\Delete(
     *       path="/contact-us/{id}",
     *       tags={"Contact Us"},
     *       operationId="delete_contact_us",
     *       summary="Delete contact us",
     *       security={{"bearerAuth":{}}},
     *       description="Delete contact us by ID",
     *       @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer"),
     *           description="The ID of the contact us"
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="Successful response",
     *           @OA\JsonContent(
     *               example={
     *                   "status": true,
     *                   "message": "success delete contact us"
     *               }
     *           )
     *       ),
     *       @OA\Response(
     *           response="404",
     *           description="contact us not found",
     *           @OA\JsonContent(
     *               example={
     *                   "status": false,
     *                   "message": "fail delete contact us",
     *                   "error": "No query results for model [App\\Models\\contactUs] 10"
     *               }
     *           )
     *       )
     *    )
     */
    public function destroy($id)
    {
        try{
            ContactUs::findOrFail($id)->delete(); 
 
            return response([
                "status" => true,
                "message" => "success delete contact us",
            ]);
 
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => "fail delete contact us",
                "error" => $th->getMessage()
            ]);
        }
    }
}
