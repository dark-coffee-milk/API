<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/reg', [UserController::class, 'registration']);
Route::post('/login', [UserController::class, 'auth']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/certificate', [CertificateController::class, 'new_certificate']);
Route::post('/certificate/{id}', [CertificateController::class, 'update_certificate']);
Route::get('/certificate', [CertificateController::class, 'all_certificate']);
Route::get('/certificate/{id}', [CertificateController::class, 'one_certificate']);
Route::delete('/certificate/{id}', [CertificateController::class, 'del_certificate']);
Route::get('/users', [UserController::class, 'users']);
Route::post('/share', [CertificateController::class, 'share']);
