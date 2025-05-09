<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User; // Add this import
use App\Mail\NewEventNotification; // Added
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Added
use Illuminate\Support\Facades\Log; // Added
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

        $event = Event::create($validated);

        try {
            // Get all users with a valid email (and optionally, only those linked to active employees)
            $users = User::whereNotNull('email')
                         ->where('email', '!=', '')
                         ->get();

            Log::info("Found " . $users->count() . " users to notify for event ID: {$event->id}");

            foreach ($users as $user) {
                Log::info("Sending event email to: " . $user->email);
                Mail::to($user->email)->send(new \App\Mail\NewEventNotification($event));
            }
            Log::info("Finished sending event emails for event ID: {$event->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send new event notification emails for event ID: {$event->id}. Error: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Event added successfully. Notifications sent.');
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
