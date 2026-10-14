@extends('errors.layout')

@section('title', '500 - Server Error')
@section('code', '500')
@section('code_badge', '500 • Server Error')
@section('badge_icon', 'fa-solid fa-server')
@section('badge_bg', '#fef2f2')
@section('badge_color', '#b91c1c')
@section('badge_border', '#fecaca')

@section('accent_color', 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)')
@section('icon_bg', 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)')
@section('icon_color', '#dc2626')
@section('icon_border', '#fca5a5')
@section('icon_glow', 'rgba(220, 38, 38, 0.22)')
@section('main_icon', 'fa-solid fa-gears')

@section('heading', 'Internal Server Error')
@section('heading_tamil', 'சர்வரில் தொழில்நுட்ப சிக்கல் ஏற்பட்டுள்ளது')
@section('description', 'An unexpected system error occurred while processing your request. Our plant technical operations team has been notified.')

@section('tips')
    <strong><i class="fa-solid fa-screwdriver-wrench" style="color: #dc2626;"></i> What to do:</strong>
    Please wait a few moments and try reloading. If you are submitting visitor feedback, the organizer staff can assist with offline check-in or manual sync.
@endsection

@section('primary_url', 'javascript:window.location.reload()')
@section('primary_icon', 'fa-solid fa-arrow-rotate-right')
@section('primary_text', 'Try Again / Reload')

@section('secondary_action', 'window.location.href="/visitor"')
@section('secondary_icon', 'fa-solid fa-house')
@section('secondary_text', 'Visitor Terminal')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-shield-halved')
@section('tertiary_text', 'Organizer Portal')
