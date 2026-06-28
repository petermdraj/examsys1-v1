@extends('layouts.app')
@section('title', $platformSettings->seo_title ?: $platformSettings->app_name)

@section('content')
@include('customer.home-themes.' . ($theme ?? 'competition'))
@endsection
