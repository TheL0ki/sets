@extends('emails.layouts.app')

@section('content')
    <div class="greeting">Hello {{ $notifiable->name }}!</div>
    
    <div class="message">
        <p>Unfortunately, your padel session has been cancelled.</p>
    </div>

    <div class="details">
        <h3 style="margin-top: 0; color: #1f2937;">Session Details</h3>
        <div class="detail-item">
            <span class="detail-label">📅 Date:</span> {{ $session->start_time->format('d.m.Y') }}
        </div>
        <div class="detail-item">
            <span class="detail-label">🕐 Time:</span> {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
        </div>
        <div class="detail-item">
            <span class="detail-label">📍 Location:</span> {{ $session->location }}
        </div>
        <div class="detail-item">
            <span class="detail-label">👥 Participants:</span> {{ $participants }}
        </div>
        @if($reason)
        <div class="detail-item">
            <span class="detail-label">❌ Reason for cancellation:</span> {{ $reason }}
        </div>
        @endif
    </div>

    <div class="message">
        <p>We apologize for any inconvenience. You can check for other available sessions in your dashboard.</p>
    </div>

    <div class="actions">
        <a href="{{ $dashboardUrl }}" class="btn">View Dashboard</a>
    </div>
@endsection
