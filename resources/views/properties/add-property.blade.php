@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">

            <div class="card-header">Add Property</div>

            <div class="card-body">

                <form id="createProperty" action="{{ route('property.create.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label for="propertyName" class="col-form-label">Property Name</label>
                        </div>

                        <div class="col">
                            <input name="property-name" id="propertyName" type="text" class="form-control @error('property-name') is-invalid @enderror" required />
                        
                            @error('property-name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label for="propertyId" class="col-form-label">Property ID</label>
                        </div>

                        <div class="col">
                            <input name="property-id" id="propertyId" type="text" class="form-control @error('property-id') is-invalid @enderror" required />
                        
                            @error('property-id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label for="propertyLogo" class="col-form-label">Logo URL</label>
                        </div>

                        <div class="col">
                            <input name="property-logo" id="propertyLogo" type="text" class="form-control @error('property-logo') is-invalid @enderror" required />
                       
                            @error('property-logo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label for="propertyUrl" class="col-form-label">Website URL</label>
                        </div>

                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text">https://</span>
                                <input name="property-url" id="propertyUrl" type="text" class="form-control @error('property-url') is-invalid @enderror" required />
                                
                                @error('property-url')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="text-end">

                        <input type="submit" class="btn btn-primary" value="Submit" />

                    </div>

                </form>

            </div>
            
        </div>
    </div>
</div>

@endsection

@section('js')
<script>

    $('#propertyLogo').change(function() {
        if ($('#propertyUrl').val() == '') {
            $('#propertyUrl').val( minifyUrl( $(this).val() ) );
        }
    });

    $('#propertyUrl').change(function() {
        $(this).val( minifyUrl( $(this).val() ) );
    });

    /**
     * Minifies a url to it's root
     *  @var String url - The input URL
     *  @return String - The minified URL
    */
    function minifyUrl(url) {

        let newUrl = url;

        // Remove http/https
        newUrl = newUrl.replace('https://', '');
        newUrl = newUrl.replace('http://', '');

        // Delete trailing / and everything after it
        let slashIndex = newUrl.indexOf('/');

        if (slashIndex != -1) {
            newUrl = newUrl.slice('0', slashIndex);
        }

        return newUrl;

    }

</script>
@endsection