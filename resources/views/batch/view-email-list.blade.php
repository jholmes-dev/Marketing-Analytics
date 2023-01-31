@extends('layouts.app')

@section('content')

<div class="sm-wrapper">

    <div class="card mb-5">

        <div class="card-body">

            <p>{{ $reports->count() }} eligible reports found. Proceeding will send an email all addresses listed below.<br/><strong>Please review the list before sending</strong>.

            <div class="text-end">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#sendEmailsConfirmation">
                    Send Emails
                </button>
            </div>

        </div>
    </div> 

    @foreach ($reports as $report)
    <div class="card mb-3">
        <div class="card-header"><strong>{{ $report->property->name }}</strong> - {{ $report->property->url }} <a class="float-end" href="{{ route('property.index', $report->property->id) }}" target="_blank">View Property</a></div>

        <div class="card-body">

            <div class="row">

                <div class="col-4">
                    <strong>Report Date<br/></strong>
                    {{ date('F, Y', strtotime($report->start_date)) }}
                </div>

                <div class="col-4">
                    <strong>Recipient: <em>{{ $report->property->getHelloLine() }}</em></strong><br/>
                    {{ $report->property->getDisplayClientEmailArray() }}
                </div>

                <div class="col-4 text-end">
                    <a href="{{ route('report.view', $report->id) }}" target="_blank">View Report</a><br/>
                    <a href="{{ route('property.email.preview', [ $report->property->id, $report->id ]) }}" target="_blank">Preview Email</a>
                </div>

            </div>

            

        </div>
    </div>
    @endforeach

</div>

<div class="modal fade" id="sendEmailsConfirmation" tabindex="-1" aria-labelledby="sendEmailsConfirmation" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Batch Email Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you'd like to send this email batch?<br/><strong>This process cannot be stopped</strong>.</p>

                <p>The following addresses will be emailed:</p>

                <table class="table table-striped">
                    <tbody>
                        @foreach ($reports as $report)
                        <tr>
                            <td>{{ $report->property->getDisplayClientEmailArray() }}</td>
                            <td>{{ $report->property->client_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>


            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('batch.email.send') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" value="{{ $month }}" name="month" />
                    <input type="hidden" value="{{ $year }}" name="year" />
                    <input type="submit" value="Send Emails" class="btn btn-primary" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection