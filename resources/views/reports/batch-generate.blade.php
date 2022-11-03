@extends('layouts.app')

@section('content')

<div class="sm-wrapper">
    <div class="card">
        <div class="card-header">Batch Generate Reports</div>

        <div class="card-body">

            <form action="{{ route('report.batch.create') }}" method="POST">
                @csrf

                <p>Please select a month/year to batch generate for. Each property without an existing report for that month will have a new report generated.</p>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <select class="form-select mb-3" name="month">
                            <option value="01" @if (date('n') - 1 == 1) selected @endif>January</option>
                            <option value="02" @if (date('n') - 1 == 2) selected @endif>February</option>
                            <option value="03" @if (date('n') - 1 == 3) selected @endif>March</option>
                            <option value="04" @if (date('n') - 1 == 4) selected @endif>April</option>
                            <option value="05" @if (date('n') - 1 == 5) selected @endif>May</option>
                            <option value="06" @if (date('n') - 1 == 6) selected @endif>June</option>
                            <option value="07" @if (date('n') - 1 == 7) selected @endif>July</option>
                            <option value="08" @if (date('n') - 1 == 8) selected @endif>August</option>
                            <option value="09" @if (date('n') - 1 == 9) selected @endif>September</option>
                            <option value="10" @if (date('n') - 1 == 10) selected @endif>October</option>
                            <option value="11" @if (date('n') - 1 == 11) selected @endif>November</option>
                            <option value="12" @if (date('n') - 1 == 0) selected @endif>December</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                    <select class="form-select mb-3" name="year">
                        @for ($i = -1; $i < 5; $i++)
                            <option value="{{ date('Y') + $i }}" @if (date('Y') + $i == date('Y', strtotime(date('Y') . ' -1 month'))) selected @endif>{{ date('Y') + $i }}</option>
                        @endfor
                    </select>
                    </div>
                </div>

                <div class="text-end">
                    <input type="submit" class="btn btn-primary" value="Generate" />
                </div>

            </form>

        </div>
    </div>

    <div class="current-batches mt-5">

        <div>
            
            @foreach (DB::table('job_batches')->orderByDesc('created_at')->get() as $rawBatch)
                @php
                    $batch = Bus::findBatch($rawBatch->id);
                @endphp
            <div id="{{ $batch->id }}" class="batch-card card mb-3">
                <div class="card-header">Job ID: {{ $batch->id }}<span class="batch-complete-title d-none float-end">Complete</span></div>
        
                <div class="card-body">
        
                    <div class="progress mb-1">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
        
                    <div class="info d-flex justify-content-between">
                        <p class="mb-0"><span id="jobsLeft">{{ $batch->pendingJobs }}</span> jobs left<br/><span id="failedJobs">{{ $batch->failedJobs }}</span> failed jobs</p>
                        <p class="text-end mb-0"><span id="processedJobs">{{ $batch->processedJobs() }}</span> / <span id="totalJobs">{{ $batch->totalJobs }}</span></p>
                    </div>

                </div>
            </div>
            @endforeach

        </div>

    </div>

</div>

@endsection

@section('js')
<script>

    let batchDataURL = '{{ route('batch.info', '') }}';

    $('document').ready(function() {
        getFreshData();
        setInterval(function() {
            getFreshData();
        }, 500);
    });

    function getFreshData() 
    {

        $('.batch-card').each(function() {

            if ($(this).hasClass('batch-completed')) {
                return;
            }

            let batchID = $(this).attr('id');
            axios.get(batchDataURL + "/" + batchID).then(function(res) {
                updateFrontend(res.data, batchID);
            });

        });

    }

    function updateFrontend(data, batchID) 
    {
        $('#progressBar', '#' + batchID).css('width', data.progress + '%');
        $('#processedJobs', '#' + batchID).html(data.processedJobs);
        $('#totalJobs', '#' + batchID).html(data.totalJobs);
        $('#jobsLeft', '#' + batchID).html(data.pendingJobs);
        $('#failedJobs', '#' + batchID).html(data.failedJobs);

        if (data.finishedAt != null) {
            $('#' + batchID).addClass('batch-completed');
        }

    }

</script>
@endsection