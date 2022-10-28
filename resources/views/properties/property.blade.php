@extends('layouts.app')

@section('content')

<div class="card">

    <div class="card-header">Generate Report</div>

    <div class="card-body">

        <form id="createProperty" action="{{ route('report.generate', $property->id) }}" method="POST">
            @csrf

            <div class="row mb-3">

                <div class="col-md-4">
                    <label for="startDate" class="col-form-label">Start Date</label>
                    <input name="start-date" id="startDate" type="date" class="form-control" value="@php echo date("Y-m-d", strtotime("first day of previous month")) @endphp" required />
                </div>

                <div class="col-md-4">
                    <label for="endDate" class="col-form-label">End Date</label>
                    <input name="end-date" id="endDate" type="date" class="form-control" value="@php echo date("Y-m-d", strtotime("last day of previous month")) @endphp" required />
                </div>

                <div class="col-md-4">
                    <label for="expDate" class="col-form-label">Report Expiration Date</label>
                    <input name="exp-date" id="expDate" type="date" class="form-control" />
                </div>

            </div>

            <div class="text-end">

                <input type="submit" class="btn btn-primary" value="Generate" />

            </div>

        </form>

    </div>
    
</div>

@php
    $sortedReports = $property->reports()->orderByDesc('created_at')->get()
@endphp
@if ($sortedReports->count())
<div class="card mt-4">

    <div class="card-header">Existing Reports</div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Month</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Expiration Date</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>

            @foreach ($sortedReports as $report)

            @php
                if ($report->report_id !== null) 
                {
                    continue; // Filter out all comparison reports
                }
            @endphp

            <tr>
                <th scope="row">{{ date('F, Y', strtotime($report->start_date)) }}</th>
                <td>{{ $report->start_date }}</td>
                <td>{{ $report->end_date }}</td>
                <td>{{ $report->exp_date ?? 'Not Set' }}</td>
                <td><a href="{{ route('report.view', $report->id) }}" target="_blank">View</a> / <a href="/" onclick="event.preventDefault(); document.getElementById('deleteReport{{ $report->id }}').submit();">Delete</a></td>

                <form id="deleteReport{{ $report->id }}" action="{{ route('report.delete', $report->id) }}" method="POST" class="d-none">
                    @csrf
                </form>
            </tr>
            @endforeach

        </tbody>
    </table>
    
</div>
@endif

@endsection

@section('sidebar')

<div class="card">

    <div class="card-header">Property Details</div>

    <div class="card-body">

        <h3>{{ $property->name }}</h3>
        <h5><a href="https://{{ $property->url }}" target="_blank">{{ $property->url }}</a></h5>
        <h5>Analytics ID: {{ $property->analytics_id }}</h5>

        <div class="mt-4 w-50 mx-auto">
            <img src="{{ $property->logo }}" alt="{{ $property->name }} Logo" />
        </div>

    </div>
    
</div>

@endsection