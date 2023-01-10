<div class="modal fade" id="deletePropertyModal" tabindex="-1" aria-labelledby="deletePropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deletePropertyModalLabel">Delete Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('property.delete', $property->id) }}">
                @csrf

                <div class="modal-body">
                    <p>Are you sure you want to delete this property? This will also delete all attached reports. <strong>This action cannot be undone.</strong></p>
                    <div class="bg-white text-center">
                        <h4>{{ $property->name }}</h4>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="submit" class="btn btn-danger" value="Delete Property">
                </div>

            </form>

        </div>
    </div>
</div>