<?php

namespace App\Http\Controllers;

/**
    * @OA\Info(
    *      version="1.0.0",
    *      title="Dokumentasi API",
    *      description="Lorem Ipsum",
    *      @OA\Contact(
    *          email="hi.wasissubekti02@gmail.com"
    *      ),
    *      @OA\License(
    *          name="Apache 2.0",
    *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
    *      )
    * )
    *
    * @OA\Server(
    *      url="http://localhost:8000/api",
    *      description="Demo API Server"
    * )
    * @OA\SecurityScheme(
    *      securityScheme="bearerAuth",
    *      type="http",
    *      scheme="bearer",
    *      bearerFormat="JWT"
 * )
    */


abstract class Controller
{
    //
}
