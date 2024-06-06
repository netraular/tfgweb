<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VirtualAssistantController;
use App\Http\Controllers\TtsController;
use App\Http\Controllers\SttController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\HistoryController;

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

Route::get('/change-language/{locale}', [LanguageController::class, 'languageSwitch'])->name('change.language');

Route::match(['get', 'post'],'/testMenu', [VirtualAssistantController::class, 'testMenu'])->name('testMenu');

Route::match(['get', 'post'],'/ttsApi', [TtsController::class, 'textToSpeechApi']);
Route::match(['get', 'post'],'/sttApi', [SttController::class, 'speechToTextApi']);
Route::match(['get', 'post'],'/ttsLocal', [TtsController::class, 'textToSpeechLocal']);
Route::match(['get', 'post'],'/sttLocal', [SttController::class, 'speechToTextLocal']);

Route::match(['get', 'post'],'/assistant', [VirtualAssistantController::class, 'main']);

Route::match(['get', 'post'],'/audioHistory', [HistoryController::class, 'showAudioHistory']);
Route::match(['get', 'post'],'/', [HistoryController::class, 'showAssistantHistory']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
