@extends('errors.layout')

@section('title', '403 - Access Denied')
@section('code', '403')
@section('code_badge', '403 • Access Restricted')
@section('badge_icon', 'fa-solid fa-shield-halved')
@section('badge_bg', '#fef2f2')
@section('badge_color', '#b91c1c')
@section('badge_border', '#fecaca')

@section('accent_color', 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)')
@section('icon_bg', 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)')
@section('icon_color', '#dc2626')
@section('icon_border', '#fca5a5')
@section('icon_glow', 'rgba(239, 68, 68, 0.18)')
@section('main_icon', 'fa-solid fa-lock')

@section('heading', 'Access Restricted / Forbidden')
@section('description', 'You do not have the required permissions or authentication to access this plant operation resource.')

@section('tips')
    <strong><i class="fa-solid fa-key" style="color: #dc2626;"></i> Security Note:</strong>
    If you are an authorized plant tour organizer or shift engineer, please log in with your staff credentials. Visitors can use the public feedback terminal.
@endsection

@section('primary_url', '/organizer/login')
@section('primary_icon', 'fa-solid fa-right-to-bracket')
@section('primary_text', 'Log In with Organizer Account')

@section('secondary_action', 'window.history.back()')
@section('secondary_icon', 'fa-solid fa-arrow-left')
@section('secondary_text', 'Go Back')

@section('tertiary_url', '/visitor')
@section('tertiary_icon', 'fa-solid fa-clipboard-check')
@section('tertiary_text', 'Visitor Terminal')
