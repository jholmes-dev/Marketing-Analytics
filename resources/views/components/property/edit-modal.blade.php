<div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editPropertyModalLabel">Edit Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('property.update', $property->id) }}">
                @csrf

                <div class="modal-body">

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="propertyName" class="col-form-label">Property Name</label>
                        </div>
                        <div class="col">
                            <input name="property-name" id="propertyName" type="text" class="form-control" value="{{ $property->name }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="propertyId" class="col-form-label">Analytics ID</label>
                        </div>
                        <div class="col">
                            <input name="property-id" id="propertyId" type="text" class="form-control" value="{{ $property->analytics_id }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="placeId" class="col-form-label">Place ID</label>
                        </div>
                        <div class="col">
                            <div class="input-group">
                                <input name="place-id" id="placeId" type="text" class="form-control" value="{{ $property->place_id }}">
                                <button id="findPlaceIdButton" data-bs-toggle="modal" data-bs-target="#findPlaceIdModal" class="btn btn-outline-secondary" type="button">Find This</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="propertyLogo" class="col-form-label">Logo URL</label>
                        </div>
                        <div class="col">
                            <input name="property-logo" id="propertyLogo" type="text" class="form-control" value="{{ $property->logo }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="propertyUrl" class="col-form-label">Website URL</label>
                        </div>
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text">https://</span>
                                <input name="property-url" id="propertyUrl" type="text" class="form-control" value="{{ $property->url }}" required>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="submit" class="btn btn-primary" value="Save changes">
                </div>

            </form>

        </div>
    </div>
</div>