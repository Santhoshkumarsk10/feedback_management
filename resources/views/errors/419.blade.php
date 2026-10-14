@extends('errors.layout')

@section('title', '419 - Session Expired')
@section('code', '419')
@section('code_badge', '419 • Security Token Expired')
@section('badge_icon', 'fa-solid fa-clock-rotate-left')
@section('badge_bg', '#fffbeb')
@section('badge_color', '#b45309')
@section('badge_border', '#fde68a')

@section('accent_color', 'linear-gradient(135deg, #b45309 0%, #f59e0b 100%)')
@section('icon_bg', 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)')
@section('icon_color', '#d97706')
@section('icon_border', '#fcd34d')
@section('icon_glow', 'rgba(245, 158, 11, 0.2)')
@section('main_icon', 'fa-solid fa-hourglass-end')

@section('heading', 'Session Expired')
@section('description', 'For plant data security and privacy, sessions automatically expire after periods of inactivity. Please refresh to renew your session.')

@section('tips')
    <strong><i class="fa-solid fa-rotate-right" style="color: #d97706;"></i> Quick Recovery:</strong>
    Tap the "Refresh & Continue" button below to renew your session token immediately without losing your connectivity.
@endsection

@section('primary_url', 'javascript:window.location.reload()')
@section('primary_icon', 'fa-solid fa-arrow-rotate-right')
@section('primary_text', 'Refresh & Continue')

@section('secondary_action', 'window.location.href="/visitor"')
@section('secondary_icon', 'fa-solid fa-house')
@section('secondary_text', 'Start New Feedback')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-user-lock')
@section('tertiary_text', 'Organizer Login')
