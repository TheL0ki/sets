@extends('emails.layouts.app')

@section('content')
    <div class="greeting">Hello {{ $notifiable->name }}!</div>
    
    <div class="message">
        <p>This is a friendly reminder about your padel session tomorrow.</p>
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
        <p>Don't forget to bring your equipment and arrive on time!</p>
    </div>

    <div class="actions">
        <a href="{{ $viewUrl }}" class="btn">View Session Details</a>
    </div>
@endsection
