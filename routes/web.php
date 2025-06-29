<?php

use App\Http\Controllers\FaceDetecionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/detect-face', [FaceDetecionController::class, 'detectFace'])->name('detect.face');
Route::view('/face-detection', 'detect-face.index')->name('face.detection.form');
