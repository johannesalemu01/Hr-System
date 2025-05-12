<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestedNotification extends Notification
{
    use Queueable;

    public LeaveRequest $leaveRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $employee = $this->leaveRequest->employee;
        $leaveType = $this->leaveRequest->leaveType;
        $url = route('leave.index');

        return (new MailMessage)
            ->subject('New Leave Request Submitted')
            ->greeting('Hello Admin,')
            ->line('A new leave request has been submitted.')
            ->line('Employee: ' . ($employee ? ($employee->first_name . ' ' . $employee->last_name) : 'N/A'))
            ->line('Leave Type: ' . ($leaveType ? $leaveType->name : 'N/A'))
            ->line('Dates: ' . $this->leaveRequest->start_date . ' to ' . $this->leaveRequest->end_date)
            ->line('Reason: ' . $this->leaveRequest->reason)
            ->action('View Leave Requests', $url)
            ->line('Thank you.');
    }
}
