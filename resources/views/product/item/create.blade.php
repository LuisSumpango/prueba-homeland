<form id="productForm">
    @csrf
    <div class="mb-3">
        <label for="code" class="form-label">Código <span style="color: red;">*Solo números y letras</span></label>
        <input type="text" class="form-control" id="code" name="code" required>
        <div id="codeError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="name" class="form-label">Nombre <span style="color: red;">*Solo letras y espacios</span></label>
        <input type="text" class="form-control" id="name" name="name" required>
        <div id="nameError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="quantity" class="form-label">Cantidad</label>
        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
    </div>
    
    <div class="mb-3">
        <label for="price" class="form-label">Precio</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" min="0" required>
    </div>
    
    <div class="mb-3">
        <label for="entry_date" class="form-label">Fecha de Ingreso (DD/MM/YYYY)</label>
        <input type="date" class="form-control" id="entry_date" name="entry_date" placeholder="DD/MM/YYYY" required>
        <div id="entryDateError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="expiration_date" class="form-label">Fecha de Vencimiento (DD/MM/YYYY)</label>
        <input type="date" class="form-control" id="expiration_date" name="expiration_date" placeholder="DD/MM/YYYY" required>
        <div id="expirationDateError" class="error-message"></div>
    </div>
    
    <div class="mb-3">
        <label for="image" class="form-label">Fotografía (JPEG, PNG, JPG, GIF - Max 1.5MB)</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/jpg, image/gif">
        <div id="imageError" class="error-message"></div>
    </div>
    
    <button type="submit" class="btn btn-primary">Guardar</button>
</form>