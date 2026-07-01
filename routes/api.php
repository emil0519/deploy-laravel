<?php

use App\Http\Controllers\EventsController;
use App\Http\Controllers\NotesController;
use Illuminate\Support\Facades\Route;

Route::post('/notes', [NotesController::class, 'store']);
Route::post('/notes/{note:uuid}', [NotesController::class, 'update']);
Route::delete('/notes/{note:uuid}', [NotesController::class, 'delete']);
Route::get('/notes', [NotesController::class, 'index']);
Route::get('/notes/{note:uuid}', [NotesController::class, 'show']);

Route::post('/events', [EventsController::class, 'store']);