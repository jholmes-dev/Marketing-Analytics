@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">{{ __('Dashboard') }}</div>

    <div class="card-body">

        @if (url()->previous() == route('login'))
            <p>If you've just logged in, don't forget to apply for Google API Credentials:</p>
            <a href="{{ route('googleOAuth.request') }}" class="btn btn-primary">Apply For Credentials</a>
        @endif

    </div>
</div>

@endsection


@section('sidebar')

<x-properties-list/>

@endsection