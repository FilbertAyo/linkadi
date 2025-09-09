<x-app-layout>
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                        <li class="breadcrumb-item" aria-current="page">Update Product</li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">Update Product</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-10">
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some problems with your input.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" value="{{ old('category', $product->category) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Images</label>
                                <input type="file" name="images[]" id="images" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">You can select multiple images. Choose one as the main image below.</small>
                            </div>

                            @if($product->mainImage)
                            <div class="col-12 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted">Current main image:</span>
                                    <img src="{{ asset('storage/' . $product->mainImage->image_path) }}" alt="Main Image" style="width: 64px; height: 64px; object-fit: cover;" class="rounded">
                                </div>
                            </div>
                            @endif

                            <div class="col-12 mt-3">
                                <div id="image-previews" class="row g-3"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const fileInput = document.getElementById('images');
            const previewsContainer = document.getElementById('image-previews');

            if (!fileInput || !previewsContainer) return;

            fileInput.addEventListener('change', function () {
                previewsContainer.innerHTML = '';
                const files = Array.from(this.files || []);

                files.forEach((file, index) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3';

                    const card = document.createElement('div');
                    card.className = 'card h-100';

                    const img = document.createElement('img');
                    img.className = 'card-img-top';
                    img.style.objectFit = 'cover';
                    img.style.height = '140px';
                    img.alt = file.name;
                    img.src = URL.createObjectURL(file);

                    const body = document.createElement('div');
                    body.className = 'card-body p-2';

                    const formCheck = document.createElement('div');
                    formCheck.className = 'form-check';

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'main_image';
                    radio.value = String(index);
                    radio.id = `main_image_${index}`;
                    radio.className = 'form-check-input';
                    if (index === 0) radio.checked = true;

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = radio.id;
                    label.textContent = 'Set as main';

                    formCheck.appendChild(radio);
                    formCheck.appendChild(label);

                    body.appendChild(formCheck);
                    card.appendChild(img);
                    card.appendChild(body);
                    col.appendChild(card);
                    previewsContainer.appendChild(col);
                });
            });
        })();
    </script>
</x-app-layout>
