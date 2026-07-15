<?php

use App\Http\Controllers\EventParticipantsController;
use App\Http\Controllers\EventsController;
use Illuminate\Support\Facades\Route;

Route::get('/events/{event:uuid}', [EventsController::class, 'show']);
Route::post('/events', [EventsController::class, 'store']);

Route::post('/events/{event:uuid}/participants', [EventParticipantsController::class, 'store']);
