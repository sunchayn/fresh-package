<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use VendorName\Skeleton\Http\Greeting\Controllers\GreetingController;

Route::post('greeting', GreetingController::class)->name('skeleton.api.greeting');
