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

@if ($reports->count())
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

            @foreach ($reports as $report)
            <tr>
                <th scope="row">{{ date('F, Y', strtotime($report->start_date)) }}</th>
                <td>{{ $report->start_date }}</td>
                <td>{{ $report->end_date }}</td>
                <td>{{ $report->exp_date ?? 'Not Set' }}</td>
                <td class="text-end"><a href="{{ route('report.view', $report->id) }}" target="_blank">View</a> / <a href="/" onclick="event.preventDefault(); document.getElementById('deleteReport{{ $report->id }}').submit();">Delete</a></td>

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

    <div class="card-header d-flex align-items-center">
        <div>Property Details</div>
        <div class="ms-auto">
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPropertyModal">Edit <i class="bi bi-pencil-square"></i></button>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePropertyModal">Delete <i class="bi bi-trash3"></i></button>
        </div>
    </div>

    <x-property.edit-modal
        :property=$property
    />
    <x-property.delete-modal
        :property=$property
    />

    <div class="card-body">

        <h3>{{ $property->name }}</h3>
        <h5><a href="https://{{ $property->url }}" target="_blank">{{ $property->url }}</a></h5>
        <h6>Analytics ID: {{ $property->analytics_id }}</h5>
        <h6>Place ID: @if ($property->place_id != NULL){{ $property->place_id }}@else <span class="text-muted">Not Set</span> @endif</h5>

        <div class="mt-4 w-60 mx-auto text-center">
            <div class="p-3 @if ($property->logo_dark_background) bg-reportdark @endif">
                <x-property.logo :property="$property" />
            </div>
        </div>

        <div class="mt-4 text-center">

            <form id="toggleLogoBackground" action="{{ route('property.logobackground.toggle', $property->id) }}" method="POST">
                @csrf
                <input type="submit" class="btn btn-outline-dark btn-sm" value="Toggle Dark Logo Background" />
            </form>

        </div>

    </div>
    
</div>

<div class="card mt-4">

    <div class="card-header align-items-center d-flex">
        Batch Email Settings 
        <i class="bi bi-question-circle-fill ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Allow the tool to email the client their report from the batch report email page."></i> 
        @if ($property->batch_email)
            <a href="#" class="btn btn-danger btn-sm ms-auto" onclick="event.preventDefault();document.getElementById('disable-batch-email').submit();">Disable</a>
        @else
            <a href="#" class="btn btn-primary btn-sm ms-auto" onclick="event.preventDefault();document.getElementById('enable-batch-email').submit();">Enable</a>
        @endif
    </div>

    <form id="enable-batch-email" action="{{ route('property.email.enable', $property->id) }}" method="POST" class="d-none">
        @csrf  
    </form>

    <form id="disable-batch-email" action="{{ route('property.email.disable', $property->id) }}" method="POST" class="d-none">
        @csrf  
    </form>
    
    @if ($property->batch_email)
    <div class="card-body">

        <form action="{{ route('property.email.update', $property->id) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="clientName">Email Recipient Name</label>
                <input name="client_name" id="clientName" type="text" class="form-control @error('client_name') is-invalid @enderror" value="{{ $property->client_name }}" />

                @error('client_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="clientEmail">Client Email <i class="bi bi-question-circle" data-bs-toggle="tooltip" data-bs-placement="right" title="The report will be sent to these emails."></i></label>
                <input type="hidden" id="existingEmailArray" value="{{ $property->getJSONClientEmailArray() }}">
                <div id="clientEmailWrapper">

                </div>

                <div class="mb-4 mt-2">
                    <button id="addClientEmail" type="button" class="btn btn-outline-success btn-sm">Add Recipient</button>
                </div>

                @error('client_email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="d-flex align-items-center justify-content-between">

                <div>
                    <a href="{{ route('property.email.preview', $property->id); }}" target="_blank">Preview Email</a>
                </div>

                <div>
                    <input type="submit" class="btn btn-primary"  value="Update" />
                </div>
            </div>

        </form>

    </div>
    @endif

</div>

<x-property.find-place-id-modal 
    submit-action="editPropertyModal"
/>

@endsection

@section('js')
<script type="module">

    // Populate the existing emails for batch email settings
    $(window).ready(function() {
        populateEmailFields(JSON.parse($('#existingEmailArray').val()));
    });

    $('#addClientEmail').click(function() {
        appendClientEmailField();
    });
    $('body').on('click', '.delete-email-field', function() {
        removeClientEmailField($(this).parent());
    });

    /**
     * Handles adding a new client email input field
     * 
     */
    function appendClientEmailField(email = '')
    {
        if (checkClientEmailMax()) return;
        $('#clientEmailWrapper').append(generateClientEmailField(email));
        checkClientEmailMax();
    }

    /**
     * Checks if we've hit the maximum amount of fields
     * 
     */
    function checkClientEmailMax()
    {
        if ($('.client-email-field').length >= 10) {
            disableAddEmailButton();
            return true;
        }
        return false;
    }

    /**
     * Removes a client email field
     * 
     * @param jQueryElement : The parent element of the field to be removed
     */
    function removeClientEmailField(ele) 
    {
        ele.remove();
        enableAddEmailButton();
    }

    /**
     * Hides the add field button
     * 
     */
    function disableAddEmailButton() 
    {
        $('#addClientEmail').hide(0);
    }

    /**
     * Shows the add field button
     * 
     */
    function enableAddEmailButton() 
    {
        $('#addClientEmail').show(0);
    }

    /** 
     * Generates and returns a new client email field
     * 
     * @param String value : The existing value, if any
     * @return jQuery element for insertion into the document
     */
    function generateClientEmailField(value = '') 
    {
        let parentEle = $('<div>').addClass(
            'input-group mb-2'
        );
        
        $('<input>').attr({
            'type': 'email',
            'name': 'client_email[]',
            'class': 'form-control client-email-field',
            'value': value
        }).appendTo(parentEle);

        $('<button>').attr({
            'class': 'delete-email-field btn btn-outline-danger',
            'type': 'button'
        }).html(
            '<i class="bi bi-x-lg"></i>'
        ).appendTo(parentEle);

        return parentEle;
    }

    /**
     * Populates the email fields with given input array
     * 
     * @param JSON : Object of emails to be inserted
     */
    function populateEmailFields(emails)
    {
        for (let i = 0; i < emails.length; i++)
        {
            appendClientEmailField(emails[i]);
        }
    }

</script>
@endsection