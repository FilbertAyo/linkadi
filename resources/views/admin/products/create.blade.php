<x-app-layout>



    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../dashboard/index.html">Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0)">Dashboard</a></li>
                        <li class="breadcrumb-item" aria-current="page">Analytics</li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">Analytics</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Add New Product</h5>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> There were some problems with your input:
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif


            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Product Information -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Product Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Product Name *</label>
                                            <input type="text"
                                                class="form-control @error('name') is-invalid @enderror" id="name"
                                                name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="sku" class="form-label">SKU *</label>
                                            <input type="text"
                                                class="form-control @error('sku') is-invalid @enderror" id="sku"
                                                name="sku" value="{{ old('sku') }}" required>
                                            @error('sku')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">TZS</span>
                                                <input type="number"
                                                    class="form-control @error('price') is-invalid @enderror"
                                                    id="price" name="price" value="{{ old('price') }}"
                                                    step="0.01" min="0" required>
                                                @error('price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                            <input type="number"
                                                class="form-control @error('stock_quantity') is-invalid @enderror"
                                                id="stock_quantity" name="stock_quantity"
                                                value="{{ old('stock_quantity') }}" min="0" required>
                                            @error('stock_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category *</label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category"
                                        name="category" required>
                                        <option value="" disabled selected>Select NFC Product Type</option>
                                        <option value="Sticker" {{ old('category') == 'Sticker' ? 'selected' : '' }}>
                                            NFC Sticker</option>
                                        <option value="Keychain" {{ old('category') == 'Keychain' ? 'selected' : '' }}>
                                            NFC Keychain</option>
                                        <option value="Wristband"
                                            {{ old('category') == 'Wristband' ? 'selected' : '' }}>NFC Wristband
                                        </option>
                                        <option value="Business Card"
                                            {{ old('category') == 'Business Card' ? 'selected' : '' }}>NFC Business
                                            Card</option>
                                        <option value="Tag" {{ old('category') == 'Tag' ? 'selected' : '' }}>NFC Tag
                                        </option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active"
                                            name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Product
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Product Images</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Upload Images</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                        id="images" name="images[]" multiple accept="image/*"
                                        onchange="previewImages(this)">
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Select multiple images. Max 2MB each.
                                    </small>
                                </div>

                                <div id="imagePreview" class="mt-3"></div>

                                <div class="mb-3" id="mainImageSelection" style="display: none;">
                                    <label for="main_image" class="form-label">Main Image</label>
                                    <select class="form-select" id="main_image" name="main_image">
                                        <!-- Options will be populated by JavaScript -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Product
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script>
        function previewImages(input) {
            const previewContainer = document.getElementById('imagePreview');
            const mainImageSelect = document.getElementById('main_image');
            const mainImageSelection = document.getElementById('mainImageSelection');

            previewContainer.innerHTML = '';
            mainImageSelect.innerHTML = '';

            if (input.files && input.files.length > 0) {
                mainImageSelection.style.display = 'block';

                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'mb-2';
                        previewDiv.innerHTML = `
                    <img src="${e.target.result}" 
                         class="img-thumbnail" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                    <small class="d-block text-muted">${file.name}</small>
                `;
                        previewContainer.appendChild(previewDiv);

                        // Add option to main image select
                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = `Image ${index + 1} (${file.name})`;
                        if (index === 0) option.selected = true;
                        mainImageSelect.appendChild(option);
                    };

                    reader.readAsDataURL(file);
                });
            } else {
                mainImageSelection.style.display = 'none';
            }
        }
    </script>

</x-app-layout>
