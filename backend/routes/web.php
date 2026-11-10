<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Laravel is running.', 200);
});
