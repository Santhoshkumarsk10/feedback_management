@extends('errors.layout')

@section('title', '404 - Page Not Found')
@section('code', '404')
@section('code_badge', '404 • Not Found')
@section('badge_icon', 'fa-solid fa-compass')
@section('badge_bg', '#eff6ff')
@section('badge_color', '#0047ba')
@section('badge_border', '#bfdbfe')

@section('accent_color', 'linear-gradient(135deg, #003366 0%, #0047ba 100%)')
@section('icon_bg', 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)')
@section('icon_color', '#0047ba')
@section('icon_border', '#bfdbfe')
@section('icon_glow', 'rgba(0, 71, 186, 0.16)')
@section('main_icon', 'fa-solid fa-magnifying-glass-location')

@section('heading', 'Page or Link Not Found')
@section('description', 'The feedback link, visitor pass, or page you requested does not exist or may have been relocated.')

@section('tips')
    <strong><i class="fa-solid fa-lightbulb" style="color: #f59e0b;"></i> Suggestions:</strong>
    Check that the URL is typed correctly, or tap below to return directly to the Visitor Feedback Home Screen or Organizer Portal.
@endsection

@section('primary_url', '/visitor')
@section('primary_icon', 'fa-solid fa-house')
@section('primary_text', 'Go to Visitor Feedback')

@section('secondary_action', 'window.history.back()')
@section('secondary_icon', 'fa-solid fa-arrow-left')
@section('secondary_text', 'Previous Page')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-user-shield')
@section('tertiary_text', 'Organizer Portal')
