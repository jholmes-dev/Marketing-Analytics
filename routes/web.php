<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/**
 * Google OAuth Handling
 * 
 */
Route::controller(GoogleOAuthController::class)->name('googleOAuth.')->group(function() {

    Route::get('/oauthrequest', 'googleOAuth2Request')->name('request');
    Route::get('/oauthresponse', 'googleOAuth2Response')->name('response');

});

/**
 * Property related routes
 * 
 */
Route::controller(PropertyController::class)->name('property.')->group(function() {

    // View individual property
    Route::get('/property/{id}/view', 'index')->name('index');

    // Create new properties
    Route::get('/property/new/', 'newIndex')->name('create.index');
    Route::post('/property/new/', 'newStore')->name('create.store');

});

/**
 * Report related routes
 * 
 */
Route::controller(ReportController::class)->middleware(['auth'])->name('report.')->group(function() {

    // Generate a new report
    Route::post('/property/{id}/report/generate', 'generateReport')->name('generate');

    // Delete a report
    Route::post('/report/{id}/delete', [ReportController::class, 'deleteReport'])->name('delete');

});

// View Report
Route::get('/report/{id}', [ReportController::class, 'getReport'])->name('report.view');

Route::get('/test', [ReportController::class, 'test']);