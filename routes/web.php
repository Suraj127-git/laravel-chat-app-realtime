<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth', 'verified'])->group(function () {
// Chat Routes
Route::get('/chat', [MessageController::class, 'index'])->name('chat');
Route::get('/messages/{user}', [MessageController::class, 'getMessages']);
Route::post('/messages/send', [MessageController::class, 'sendMessage']);
// });

// require __DIR__.'/auth.php';
