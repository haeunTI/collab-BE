<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Http\Requests\StoreContactUsRequest;
use App\Http\Requests\UpdateContactUsRequest;
use Carbon\Carbon;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
