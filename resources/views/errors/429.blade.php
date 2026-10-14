@extends('errors.layout')

@section('title', '429 - Rate Limit Exceeded')
@section('code', '429')
@section('code_badge', '429 • Too Many Requests')
@section('badge_icon', 'fa-solid fa-gauge-high')
@section('badge_bg', '#fff7ed')
@section('badge_color', '#c2410c')
@section('badge_border', '#fed7aa')

@section('accent_color', 'linear-gradient(135deg, #c2410c 0%, #f97316 100%)')
@section('icon_bg', 'linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%)')
@section('icon_color', '#ea580c')
@section('icon_border', '#fdba74')
@section('icon_glow', 'rgba(234, 88, 12, 0.2)')
@section('main_icon', 'fa-solid fa-hand')

@section('heading', 'Too Many Requests')
@section('description', 'You have sent too many requests in a short period of time. Please wait a brief moment before trying again.')

@section('tips')
    <strong><i class="fa-solid fa-stopwatch" style="color: #ea580c;"></i> Rate Limiting Protection:</strong>
    To ensure smooth service for all plant tablets and visitor terminals, submission rates are throttled. Please pause for 30 seconds.
@endsection

@section('primary_url', 'javascript:setTimeout(function(){window.location.reload();}, 3000)')
@section('primary_icon', 'fa-solid fa-arrow-rotate-right')
@section('primary_text', 'Wait 30s & Retry')

@section('secondary_action', 'window.location.href="/visitor"')
@section('secondary_icon', 'fa-solid fa-house')
@section('secondary_text', 'Visitor Home')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-user-shield')
@section('tertiary_text', 'Organizer Login')
