@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Create Product</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif  

  <form action="{{ route('inventory.store') }}" method="POST">
    @csrf

    <div class="mb-3">
      <label for="productCode" class="form-label">Product Code</label>
      <input type="text" class="form-control" id="productCode" name="productCode" value="{{ old('productCode') }}" maxlength="16" required>
    </div>

    <div class="mb-3">
      <label for="productName" class="form-label">Product Name</label>
      <input type="text" class="form-control" id="productName" name="productName" value="{{ old('productName') }}" maxlength="255" required>
    </div>

    <div class="mb-3">
      <label for="productPrice" class="form-label">Price</label>
      <input type="number" class="form-control" id="productPrice" name="productPrice" value="{{ old('productPrice') }}"  max="9999999999" required>
    </div>

    <div class="mb-3">
      <label for="productCost" class="form-label">Cost</label>
      <input type="number" class="form-control" id="productCost" name="productCost" value="{{ old('productCost') }}" max="9999999999" required>
    </div>

    <div class="mb-3">
      <label for="LeadTime" class="form-label">Lead Time (Day)</label>
      <input type="number" class="form-control" id="LeadTime" name="LeadTime" value="{{ old('LeadTime') }}" maxlength="365">
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock</label>
      <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}">
    </div>

    <div class="mb-3">
      <label for="minStock" class="form-label">Minimum Stock</label>
      <input type="number" class="form-control" id="minStock" name="minStock" value="{{ old('minStock') }}">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Save</button>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
