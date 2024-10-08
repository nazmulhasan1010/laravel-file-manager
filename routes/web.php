<?php

use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('nf-file-manager', [FileManagerController::class, 'index'])->name('nf.home');
Route::post('nf-file-manager/items', [FileManagerController::class, 'items'])->name('nf.items');
Route::post('nf-file-manager/settings', [FileManagerController::class, 'settingsUpdate'])->name('nf.settings');
Route::post('nf-file-manager/add', [FileManagerController::class, 'add'])->name('nf.add');
Route::post('nf-file-manager/rename', [FileManagerController::class, 'rename'])->name('nf.rename');
Route::post('nf-file-manager/rearrange', [FileManagerController::class, 'rearrange'])->name('nf.rearrange');
Route::delete('nf-file-manager/delete', [FileManagerController::class, 'delete'])->name('nf.delete');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__.'/auth.php';
