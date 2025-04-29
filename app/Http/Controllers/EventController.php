<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $event->event_date->format('M d, Y h:i A'),
                'type' => $event->type,
                'description' => $event->description,
            ],
        ]);
    }
}
