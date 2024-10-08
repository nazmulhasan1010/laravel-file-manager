<?php

use Illuminate\Support\Facades\Route;

Route::get('manager', function () {
    return view('nh-file-manager.file-manager');
});
