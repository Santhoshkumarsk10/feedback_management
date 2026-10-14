@extends('errors.layout')

@section('title', '503 - System Maintenance')
@section('code', '503')
@section('code_badge', '503 • Scheduled Maintenance')
@section('badge_icon', 'fa-solid fa-screwdriver-wrench')
@section('badge_bg', '#f0fdf4')
@section('badge_color', '#15803d')
@section('badge_border', '#bbf7d0')

@section('accent_color', 'linear-gradient(135deg, #15803d 0%, #10b981 100%)')
@section('icon_bg', 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)')
@section('icon_color', '#16a34a')
@section('icon_border', '#86efac')
@section('icon_glow', 'rgba(22, 163, 74, 0.2)')
@section('main_icon', 'fa-solid fa-traffic-cone')
@section('status_pill', 'System Updating')

@section('heading', 'Under Scheduled Maintenance')
@section('heading_tamil', 'கணினி திட்டமிடப்பட்ட பராமரிப்பில் உள்ளது')
@section('description', 'The Shibaura Machine Plant Feedback System is temporarily undergoing routine upgrades and synchronization. Service will resume shortly.')

@section('tips')
    <strong><i class="fa-solid fa-clock" style="color: #16a34a;"></i> Maintenance in progress:</strong>
    All visitor records and past feedback data remain secure in plant storage. Please check back in a few minutes.
@endsection

@section('primary_url', 'javascript:window.location.reload()')
@section('primary_icon', 'fa-solid fa-arrow-rotate-right')
@section('primary_text', 'Check Status / Refresh')

@section('secondary_action', 'window.history.back()')
@section('secondary_icon', 'fa-solid fa-arrow-left')
@section('secondary_text', 'Go Back')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-shield')
@section('tertiary_text', 'Admin Access')
