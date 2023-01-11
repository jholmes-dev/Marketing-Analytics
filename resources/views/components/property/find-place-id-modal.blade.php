@php
/**
 * Modal for finding/generating a Google Maps Place ID
 */
@endphp
<div class="modal fade" id="findPlaceIdModal" tabindex="-1" aria-labelledby="findPlaceIdLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="findPlaceIdLabel">Find Place ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body mb-3">
                <input id="placeIdSearchBox" type="text" class="form-control mb-2" placeholder="Place Search">
                <input id="placeIdSearchResult" type="text" class="form-control" value="Place ID" disabled readonly>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" {!! $dataAttributes !!}>Cancel</button>
                <button type="button" class="btn btn-primary" id="placeIdSearchInsert" {!! $dataAttributes !!}>Insert</button>
            </div>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_places_js_api_key') }}&libraries=places&callback=initMap"></script>
<script src="{{ Vite::asset('resources/js/place-lookup.js') }}" type="module"></script>