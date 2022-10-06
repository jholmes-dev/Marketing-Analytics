<div class="card">
    <div class="card-header">Existing Properties</div>

    <div class="card-body p-0">

        <div id="propertiesList">
            
            <div class="pl-search p-3">
                <input id="propertySearch" class="form-control" type="text" placeholder="Search for properties..." />
            </div>

            <div class="pl-items">

                @foreach ($properties as $property) 
                
                <div class="pl-item my-3 position-relative">
                    <div class="row">

                        <div class="pli-name col">
                            {{ $property->name }}
                        </div>

                        <div class="pli-id col-auto">
                            {{ $property->analytics_id }}
                        </div>

                        <div class="pli-url col-12">
                            {{ $property->url }}
                        </div>

                    </div>

                    <a href="{{ route('property.index', $property->id) }}" class="stretched-link"></a>
                </div>

                @endforeach

            </div>

        </div>

    </div>
</div>