<?php

namespace App\Http\Controllers;

use App\Actions\CreateEventParticipantsAction;
use App\Http\Requests\CreateEventParticipantsRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EventParticipantsController extends Controller
{
    public function store(CreateEventParticipantsRequest $request, Event $event, CreateEventParticipantsAction $action): JsonResponse
    {
        $event = $action->handle($event, $request->participant_name, $request->responses);

        return EventResource::make($event->load('options'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
