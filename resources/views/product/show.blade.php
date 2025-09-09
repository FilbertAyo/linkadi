@extends('layouts.front-app')

@section('content')

		<div class="hero">
			<div class="container">
				<div class="row justify-content-between">
					<div class="col-lg-5">
						<div class="intro-excerpt">
							<h1>{{ $product->name }}</h1>
						</div>
					</div>
					<div class="col-lg-7">

					</div>
				</div>
			</div>
		</div>

		<div class="untree_co-section">
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						@if($product->mainImage)
							<img src="{{ Storage::url($product->mainImage->image_path) }}" class="img-fluid mb-3" alt="{{ $product->name }}">
						@endif
						<div class="row">
							@foreach($product->images as $image)
							<div class="col-3 mb-2">
								<img src="{{ Storage::url($image->image_path) }}" class="img-fluid" alt="{{ $product->name }}">
							</div>
							@endforeach
						</div>
					</div>
					<div class="col-md-6">
						<h2 class="mb-3">${{ number_format($product->price, 2) }}</h2>
						<p>{{ $product->description }}</p>
						<form method="POST" action="{{ route('cart.add', $product) }}" class="mt-3 d-flex" style="max-width:260px;">
							@csrf
							<input type="number" name="quantity" min="1" value="1" class="form-control me-2">
							<button type="submit" class="btn btn-black">Add to Cart</button>
						</form>
					</div>
				</div>
			</div>
		</div>

@endsection


