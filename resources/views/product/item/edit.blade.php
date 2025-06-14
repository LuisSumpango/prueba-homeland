<form id="productForm">
    @csrf
    @method('PUT')
    
    <div class="mb-3">
        <label for="code" class="form-label">Código <span style="color: red;">*Solo números y letras</span></label>
        <input type="text" class="form-control" id="code" name="code" value="{{ $product->code }}">
        <div id="codeError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="name" class="form-label">Nombre <span style="color: red;">*Solo letras y espacios</span></label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" required>
        <div id="nameError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="quantity" class="form-label">Cantidad</label>
        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="{{ $product->quantity }}" required>
    </div>
    
    <div class="mb-3">
        <label for="price" class="form-label">Precio</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" min="0" value="{{ $product->price }}" required>
    </div>
    
    <div class="mb-3">
        <label for="entry_date" class="form-label">Fecha de Ingreso</label>
        <input type="date" class="form-control" id="entry_date" name="entry_date" value="{{ $product->entry_date }}" required>
        <div id="entryDateError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="expiration_date" class="form-label">Fecha de Vencimiento</label>
        <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="{{ $product->expiration_date }}" required>
        <div id="expirationDateError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="image" class="form-label">Fotografía (JPEG, PNG, JPG, GIF - Max 1.5MB)</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/jpg, image/gif">
        <div id="imageError" class="error-message"></div>
        
        @if($product->image_path)
            <div class="mt-3">
                <p>Imagen actual:</p>
                <img src="{{ asset('product_images/'.$product->image_path) }}" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                    <label class="form-check-label" for="remove_image">
                        Eliminar imagen actual
                    </label>
                </div>
            </div>
        @endif
    </div>
    
    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </div>
</form>