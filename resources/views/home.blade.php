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
    <div class="card-header">{{ __('Recent Reports') }}</div>

    <div class="card-body">

        @if ($reports->count())
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
                        <td class="text-end"><a href="{{ route('report.view', $report->id) }}" target="_blank">Report</a> / <a href="{{ route('property.index', $report->property->id) }}">Property</a></td>
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