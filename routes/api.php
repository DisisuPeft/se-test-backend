<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IneController; 

//Auth views
Route::post('/v1/auth/login', [AuthController::class, 'login']);

Route::prefix('/v1')->middleware('auth:sanctum')->group(function (){
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    //subida
    Route::post('/new/colab', [IneController::class, 'extract']);
    //get
    Route::get('/colaboradores', [IneController::class, 'getColab']);
});

