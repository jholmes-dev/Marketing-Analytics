@extends('layouts.app')

@section('content')

<div class="sm-wrapper">
    <div class="card">
        <div class="card-header">Batch Email List</div>

        <div class="card-body">

            <form action="{{ route('batch.email.generate.redirect') }}" method="POST">
                @csrf

                <p>Please select the month & year you'd like to view eligible batch reports for. This does not send emails.</p>

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
                        @for ($i = -2; $i <= 0; $i++)
                            <option value="{{ date('Y') + $i }}" @if (date('Y') + $i == date('Y', strtotime(date('Y') . ' -1 month'))) selected @endif>{{ date('Y') + $i }}</option>
                        @endfor
                    </select>
                    </div>
                </div>

                <div class="text-end">
                    <input type="submit" class="btn btn-primary" value="View List" />
                </div>

            </form>

        </div>
    </div>
</div>

@endsection