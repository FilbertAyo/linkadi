@extends('layouts.front-app')

@section('content')

		<!-- Start Hero Section -->
			<div class="hero">
				<div class="container">
					<div class="row justify-content-between">
						<div class="col-lg-5">
							<div class="intro-excerpt">
								<h1>Shop</h1>
							</div>
						</div>
						<div class="col-lg-7">

						</div>
					</div>
				</div>
			</div>
		<!-- End Hero Section -->



		<div class="untree_co-section product-section before-footer-section">
		    <div class="container">
		      	<div class="row">
                @foreach($products as $product)
                <div class="col-12 col-md-4 col-lg-3 mb-5">
                    <div class="product-item">
                        <a href="{{ route('product.show', $product) }}">
                            <img src="{{ $product->mainImage ? Storage::url($product->mainImage->image_path) : asset('images/product-1.png') }}" class="img-fluid product-thumbnail">
                            <h3 class="product-title">{{ $product->name }}</h3>
                            <strong class="product-price">${{ number_format($product->price, 2) }}</strong>
                        </a>
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-black">Add to Cart</button>
                        </form>
                    </div>
                </div>
                @endforeach
		      	</div>
		    </div>
		</div>



@endsection
