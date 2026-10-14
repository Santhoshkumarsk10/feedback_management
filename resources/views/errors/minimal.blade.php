@extends('errors.layout')

@section('title', ($exception ? $exception->getStatusCode() : 'Error') . ' - Plant Feedback')
@section('code', $exception ? $exception->getStatusCode() : '500')
@section('code_badge', ($exception ? $exception->getStatusCode() : 'Error') . ' • System Notice')
@section('badge_icon', 'fa-solid fa-triangle-exclamation')
@section('badge_bg', '#eff6ff')
@section('badge_color', '#0047ba')
@section('badge_border', '#bfdbfe')

@section('accent_color', 'linear-gradient(135deg, #003366 0%, #0047ba 100%)')
@section('icon_bg', 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)')
@section('icon_color', '#0047ba')
@section('icon_border', '#bfdbfe')
@section('icon_glow', 'rgba(0, 71, 186, 0.16)')
@section('main_icon', 'fa-solid fa-circle-exclamation')

@section('heading', $message ?? 'System Notice')
@section('heading_tamil', 'கணினி அறிவிப்பு மற்றும் தகவல்')
@section('description', $exception && $exception->getMessage() ? $exception->getMessage() : 'An unexpected condition was encountered during this request.')

@section('primary_url', '/visitor')
@section('primary_icon', 'fa-solid fa-house')
@section('primary_text', 'Return to Visitor Home')

@section('secondary_action', 'window.location.reload()')
@section('secondary_icon', 'fa-solid fa-arrow-rotate-right')
@section('secondary_text', 'Reload')

@section('tertiary_url', '/organizer/login')
@section('tertiary_icon', 'fa-solid fa-user-shield')
@section('tertiary_text', 'Organizer Portal')
