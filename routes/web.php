<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BatchController;

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

Route::get('/register', function() {
    return redirect()->route('login');
});
Route::post('/register', function() {
    return redirect()->route('login');
});

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

    // Edit a property
    Route::post('/property/{id}/edit', 'update')->name('update');

    // Delete a property
    Route::post('/property/{id}/delete', 'delete')->name('delete');

    // Toggle batch email flag
    Route::post('/property/{id}/email/enable', 'enableBatchEmail')->name('email.enable');
    Route::post('/property/{id}/email/disable', 'disableBatchEmail')->name('email.disable');

    // Update batch email settings
    Route::post('/property/{id}/email/update', 'updateBatchEmailSettings')->name('email.update');

    // Toggle property's logo background option
    Route::post('/property/{id}/logobackground/toggle', 'toggleDarkLogoBackground')->name('logobackground.toggle');

    // Preview a property's email template
    Route::get('/property/{id}/email/preview/{reportId?}', 'previewReportEmail')->name('email.preview');

});

/**
 * Report related routes
 * 
 */
Route::controller(ReportController::class)->middleware(['auth'])->name('report.')->group(function() {

    // Generate a new report
    Route::post('/property/{id}/report/generate', 'generateReport')->name('generate');

    // Batch report generation
    Route::get('/report/batch', 'batchGenerateView')->name('batch.view');
    Route::post('/report/batch', 'batchGenerateCreate')->name('batch.create');

    // Delete a report
    Route::post('/report/{id}/delete', [ReportController::class, 'deleteReport'])->name('delete');

    // View a batch report batch
    Route::get('/view/batch/{id}', 'viewBatchReportJob')->name('batch.viewjob');

});

// View Report Public
Route::get('/report/{id}', [ReportController::class, 'getReport'])->name('report.view');

/**
 * Batch related routes
 * 
 */
Route::controller(BatchController::class)->middleware(['auth'])->name('batch.')->prefix('batch')->group(function() {

    // Generate a list of batch emails for a month
    Route::get('/email/generate', 'generateEmailListView')->name('email.generate');
    Route::post('/email/generate', 'generateEmailListRedirect')->name('email.generate.redirect');

    // View list of eligible batch emails for a given month/year
    Route::get('/email/view/{year}/{month}', 'showBatchEmailList')->name('email.view');

    // Send emails from batch
    Route::post('/email/send', 'sendBatchEmails')->name('email.send');

});