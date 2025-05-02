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

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Event::create($validated);

        return redirect()->back()->with('success', 'Event added successfully.');
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $event->update($validated);

        
        return redirect()->back();
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        
        return redirect()->back(); 
        
        
    }
}
