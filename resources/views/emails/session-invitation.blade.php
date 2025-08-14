@extends('emails.layouts.app')

@section('content')
    <div class="greeting">Hello {{ $notifiable->name }}!</div>
    
    <div class="message">
        <p>You have been invited to join a padel session.</p>
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
    </div>

    <div class="message">
        <p>Please respond to this invitation as soon as possible.</p>
    </div>

    <div class="actions">
        <a href="{{ $acceptUrl }}" class="btn">Accept Invitation</a>
        <a href="{{ $declineUrl }}" class="btn btn-danger">Decline Invitation</a>
    </div>

    <div class="message" style="text-align: center; margin-top: 20px;">
        <p>You can also view the session details by clicking the button below.</p>
        <a href="{{ $viewUrl }}" class="btn btn-secondary">View Session Details</a>
    </div>
@endsection
