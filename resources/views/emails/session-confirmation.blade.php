@extends('emails.layouts.app')

@section('content')
    <div class="greeting">Hello {{ $notifiable->name }}!</div>
    
    <div class="message">
        <p>Great news! Your padel session has been confirmed.</p>
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
    </div>

    <div class="message">
        <p>The session is now confirmed and all participants have accepted. You can now create matches and manage the session.</p>
    </div>

    <div class="actions">
        <a href="{{ $viewUrl }}" class="btn">View Session Details</a>
    </div>
@endsection
