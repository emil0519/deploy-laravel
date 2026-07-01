<?php

namespace App\Http\Controllers;

use App\Actions\CreateEventAction;
use App\Http\Requests\CreateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EventsController extends Controller
{
    public function store(CreateEventRequest $request, CreateEventAction $action): JsonResponse
    {
        $event = $action->handle($request->validated());

        return EventResource::make($event->load('options'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
