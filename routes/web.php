<?php

use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::prefix('nf-file-manager')->name('nf.')->group(function () {
    Route::get('/', [FileManagerController::class, 'index'])->name('home');
    Route::post('items', [FileManagerController::class, 'items'])->name('items');
    Route::get('get-info', [FileManagerController::class, 'information'])->name('get-info');
    Route::post('settings', [FileManagerController::class, 'settingsUpdate'])->name('settings');
    Route::post('add', [FileManagerController::class, 'add'])->name('add');
    Route::post('rename', [FileManagerController::class, 'rename'])->name('rename');
    Route::post('rearrange', [FileManagerController::class, 'rearrange'])->name('rearrange');
    Route::delete('delete', [FileManagerController::class, 'delete'])->name('delete');
});

