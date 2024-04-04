<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VirtualAssistantController;
use App\Http\Controllers\TtsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('testMenu');
// });

Auth::routes();
Route::match(['get', 'post'],'/', [VirtualAssistantController::class, 'testMenu'])->name('/');
Route::match(['get', 'post'],'/generar-audio', [TtsController::class, 'generarAudioApi']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

