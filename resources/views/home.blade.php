@extends('layouts.app')

@section('content')

@if (url()->previous() == route('login'))
<div class="card mb-4 text-center">
    <div class="card-body">
        <p>If you've just logged in, don't forget to apply for Google API Credentials:</p>
        <a href="{{ route('googleOAuth.request') }}" class="btn btn-primary">Apply For Credentials</a>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">{{ __('Dashboard') }}</div>

    <div class="card-body">



        @if ($reports->count())
            <h3>Recent Reports</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Property</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">End Date</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
        
                    @foreach ($reports as $report)
                    <tr>
                        <th scope="row">{{ $report->property->name }}</th>
                        <td>{{ $report->start_date }}</td>
                        <td>{{ $report->end_date }}</td>
                        <td><a href="{{ route('report.view', $report->id) }}" target="_blank">View</a> / <a href="/" onclick="event.preventDefault(); document.getElementById('deleteReport{{ $report->id }}').submit();">Delete</a></td>
        
                        <form id="deleteReport{{ $report->id }}" action="{{ route('report.delete', $report->id) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </tr>
                    @endforeach
        
                </tbody>
            </table>
        @endif

    </div>
</div>

@endsection


@section('sidebar')

<x-properties-list/>

@endsection