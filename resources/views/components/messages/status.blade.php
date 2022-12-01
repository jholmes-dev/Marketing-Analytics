@if (session('error'))

	<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
		<i class="bi bi-exclamation-circle fs-4 me-2 lh-1"></i>
		<div>
			@php
			
				if (gettype(session('error')) == 'object' && get_class(session('error')) == 'Google\Service\Exception') 
				{
					foreach (session('error')->getErrors() as $e) 
					{
						echo $e['message'];
					}
				}
				else 
				{
					echo session('error');
				}
			
			@endphp
		</div>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif

@if (session('status'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		{{ session('status') }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif

@if (session('success'))
	<div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
		<i class="bi bi-check2-circle fs-4 me-2 lh-1"></i>
		<div>{{ session('success') }}</div>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif

@if (session('GoogleAPIErrors'))
	@foreach (session('GoogleAPIErrors') as $e)
		<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
			<i class="bi bi-exclamation-circle fs-4 me-2 lh-1"></i>
			<div>{{ $e['message'] }}</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	@endforeach
@endif