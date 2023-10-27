<?php

use App\Http\Controllers\CookierController;
use App\Http\Controllers\LTIController;
use Illuminate\Support\Facades\Route;


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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('lti')->name('lti.')->group(function () {
    Route::get('/', [LTIController::class, 'greeting']);
    Route::get('jwks', [LTIController::class, 'jwks']);
    Route::post('login', [LTIController::class, 'login']);
    Route::post('launches', [LTIController::class, 'handleRedirectAfterLogin']);
    Route::get('launches', [LTIController::class, 'greeting'])->name('launch');
    Route::get('deeplink-select', [LTIController::class, 'selectQuizLevel']);
    Route::get('do-quiz', [LTIController::class, 'doQuiz']);
    Route::post('submit-quiz', [LTIController::class, 'handleQuizSubmitted']);
    Route::get('quiz-completed',  [LTIController::class, 'quizCompleted']);
});


Route::get('cookier', [CookierController::class, 'index']);
Route::get('get-cookier',  [CookierController::class, 'getCookie']);
