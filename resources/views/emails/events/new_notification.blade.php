@component('mail::message')
# New Event Announcement: {{ $eventTitle }}

Hello,

We're excited to announce a new event:

**Event:** {{ $eventTitle }}
**Date & Time:** {{ $eventDate }}
**Type:** {{ $eventType }}

@if($eventDescription)
**Description:**
{{ $eventDescription }}
@endif

You can view more details on the company dashboard or by clicking the button below.

@component('mail::button', ['url' => $url])
View Event Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
