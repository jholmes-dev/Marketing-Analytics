@if ($property->logo)
    <img src="{{ $property->logo }}" alt="{{ $property->name }} Logo" />
@else 
    <h2 class="mb-0">{{ $property->name }}</h2>
@endif