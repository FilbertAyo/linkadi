
@extends('layouts.front-app')

@section('content')


		<!-- Start Hero Section -->
			<div class="hero">
				<div class="container">
					<div class="row justify-content-between">
						<div class="col-lg-5">
							<div class="intro-excerpt">
								<h1>Cart</h1>
							</div>
						</div>
						<div class="col-lg-7">

						</div>
					</div>
				</div>
			</div>
		<!-- End Hero Section -->



		<div class="untree_co-section before-footer-section">
            <div class="container">
              <div class="row mb-5">
                <form class="col-md-12" method="post">
                  <div class="site-blocks-table">
                    <table class="table">
                      <thead>
                        <tr>
                          <th class="product-thumbnail">Image</th>
                          <th class="product-name">Product</th>
                          <th class="product-price">Price</th>
                          <th class="product-quantity">Quantity</th>
                          <th class="product-total">Total</th>
                          <th class="product-remove">Remove</th>
                        </tr>
                      </thead>
                      <tbody>
                      @php($subtotal = 0)
                      @forelse(($cart ?? []) as $item)
                        @php($line = $item['price'] * $item['quantity'])
                        @php($subtotal += $line)
                        <tr>
                          <td class="product-thumbnail">
                            <img src="{{ $item['image_url'] }}" alt="Image" class="img-fluid">
                          </td>
                          <td class="product-name">
                            <h2 class="h5 text-black">{{ $item['name'] }}</h2>
                          </td>
                          <td>${{ number_format($item['price'], 2) }}</td>
                          <td>
                            <form method="POST" action="{{ route('cart.update', $item['id']) }}">
                              @csrf
                              <div class="input-group mb-3 d-flex align-items-center quantity-container" style="max-width: 180px;">
                                <input type="number" name="quantity" min="0" class="form-control text-center quantity-amount js-qty" value="{{ $item['quantity'] }}" aria-label="Quantity" data-price="{{ number_format($item['price'], 2, '.', '') }}" data-line-target="#line-{{ $item['id'] }}" data-subtotal-target="#subtotal-value">
                                <button class="btn btn-outline-black" type="submit">Update</button>
                              </div>
                            </form>
                          </td>
                          <td id="line-{{ $item['id'] }}">${{ number_format($line, 2) }}</td>
                          <td>
                            <form method="POST" action="{{ route('cart.remove', $item['id']) }}">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-black btn-sm">X</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="6" class="text-center">Your cart is empty.</td>
                        </tr>
                      @endforelse
                      </tbody>
                    </table>
                  </div>
                </form>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="row mb-5">
                    <div class="col-md-6 mb-3 mb-md-0">
                      <button class="btn btn-black btn-sm btn-block">Update Cart</button>
                    </div>
                    <div class="col-md-6">
                      <button class="btn btn-outline-black btn-sm btn-block">Continue Shopping</button>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="text-black h4" for="coupon">Coupon</label>
                      <p>Enter your coupon code if you have one.</p>
                    </div>
                    <div class="col-md-8 mb-3 mb-md-0">
                      <input type="text" class="form-control py-3" id="coupon" placeholder="Coupon Code">
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-black">Apply Coupon</button>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 pl-5">
                  <div class="row justify-content-end">
                    <div class="col-md-7">
                      <div class="row">
                        <div class="col-md-12 text-right border-bottom mb-5">
                          <h3 class="text-black h4 text-uppercase">Cart Totals</h3>
                        </div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <span class="text-black">Subtotal</span>
                        </div>
                        <div class="col-md-6 text-right">
                          <strong class="text-black" id="subtotal-value">${{ number_format($subtotal, 2) }}</strong>
                        </div>
                      </div>
                      <div class="row mb-5">
                        <div class="col-md-6">
                          <span class="text-black">Total</span>
                        </div>
                        <div class="col-md-6 text-right">
                          <strong class="text-black">${{ number_format($subtotal, 2) }}</strong>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <a class="btn btn-black btn-lg py-3 btn-block" href="{{ route('cart.checkout') }}">Proceed To Checkout</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


		@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const qtyInputs = document.querySelectorAll('.js-qty');
  function recalcSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('[id^="line-"]').forEach(function (el) {
      const value = parseFloat(String(el.textContent).replace(/[^0-9.\-]/g, '')) || 0;
      subtotal += value;
    });
    const subtotalEl = document.querySelector('#subtotal-value');
    if (subtotalEl) {
      subtotalEl.textContent = '$' + subtotal.toFixed(2);
    }
  }
  qtyInputs.forEach(function (input) {
    input.addEventListener('input', function () {
      const price = parseFloat(input.dataset.price || '0');
      const qty = Math.max(0, parseInt(input.value || '0', 10));
      const lineTarget = document.querySelector(input.dataset.lineTarget);
      if (lineTarget) {
        const line = price * qty;
        lineTarget.textContent = '$' + line.toFixed(2);
      }
      recalcSubtotal();
    });
  });
});
</script>
@endpush
