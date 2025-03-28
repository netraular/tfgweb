<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VirtualAssistantController;
use App\Http\Controllers\TtsController;
use App\Http\Controllers\SttController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ComparatorController;
use App\Http\Controllers\HomeController;
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

Route::match(['get', 'post'],'/checkCorrectAnswers', [VirtualAssistantController::class, 'checkCorrectAnswers']);


Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/change-language/{locale}', [LanguageController::class, 'languageSwitch'])->name('change.language');

Route::match(['get', 'post'],'/assistant', [VirtualAssistantController::class, 'main']);

Route::match(['get', 'post'],'/', [VirtualAssistantController::class, 'interactAssistant']);

Route::match(['get', 'post'],'/testMenu', [VirtualAssistantController::class, 'testMenu'])->name('testMenu');
// Route::match(['get', 'post'],'/ttsApi', [TtsController::class, 'textToSpeechApi']);
// Route::match(['get', 'post'],'/sttApi', [SttController::class, 'speechToTextApi']);
// Route::match(['get', 'post'],'/ttsLocal', [TtsController::class, 'textToSpeechLocal']);
// Route::match(['get', 'post'],'/sttLocal', [SttController::class, 'speechToTextLocal']);

Route::match(['get', 'post'],'/audioHistory', [HistoryController::class, 'showAudioHistory']);
Route::match(['get', 'post'],'/assistantHistory', [HistoryController::class, 'showAssistantHistory']);

Route::match(['get', 'post'], '/comparisons', [ComparatorController::class, 'showComparisons'])->name('showComparisons');
// Route::post('/comparisons', [ComparatorController::class, 'updateAnswer'])->name('comparisons.updateAnswer');




