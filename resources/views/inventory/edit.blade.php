@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Edit Product</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif  

  <form action="{{ route('inventory.update', $product->productID) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="productCode" class="form-label">Product Code</label>
      <input type="text" class="form-control" id="productCode" name="productCode"
        value="{{ old('productCode', $product->productCode) }}" maxlength="16" required>
    </div>

    <div class="mb-3">
      <label for="productName" class="form-label">Product Name</label>
      <input type="text" class="form-control" id="productName" name="productName"
        value="{{ old('productName', $product->productName) }}" maxlength="255" required>
    </div>

    <div class="mb-3">
      <label for="productPrice" class="form-label">Price</label>
      <input type="number" class="form-control" id="productPrice" name="productPrice"
        value="{{ old('productPrice', $product->productPrice) }}" max="9999999999" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Cost</label>
      <input type="number" class="form-control" name="productCost" value="{{ old('productCost', $product->getHPP()) }}" max="9999999999" required>

    </div>


    <div class="mb-3">
      <label for="LeadTime" class="form-label">Lead Time (Day)</label>
      <input type="number" class="form-control" id="LeadTime" name="LeadTime"
        value="{{ old('LeadTime', $product->LeadTime) }}" maxlength="365">
    </div>

    <div class="mb-3">
      <label class="form-label">Stock</label>
      <input type="number" class="form-control" name="stock" value="{{ old('stock', $product->getStock()) }}" required>

    </div>

    <div class="mb-3">
      <label for="minStock" class="form-label">Minimum Stock</label>
      <input type="number" class="form-control" id="minStock" name="minStock"
        value="{{ old('minStock', $product->minStock) }}">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Update</button>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
