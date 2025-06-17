<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use  App\Models\Contact;
use Illuminate\Http\Request;

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

Route::get('/api/contacts', function (Request $request) {
    return Contact::whereIn('id', $request->ids)->select('id', 'name','email')->get();
});
Route::post('/contacts/merge', [ContactController::class, 'merge'])->name('contact.merge');
Route::resource('contact', ContactController::class);
