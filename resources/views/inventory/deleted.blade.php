@extends('layouts.main')

@section('container')

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Deleted Inventory</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
      <a href="{{ route('inventory.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>
  
<table class="table table-bordered">
    <thead CLASS= "table-dark text-center">
      <tr>
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Stock</th>
        <th>Price</th>
        <th>Cost</th>
        <th>Min Stock</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      @foreach($products as $product)
        <tr>
          <td>{{ $product->productCode }}</td>
          <td>{{ $product->productName }}</td>
          <td>{{ $product->getStock() }}</td>
          <td>{{ number_format($product->productPrice) }}</td>
          <td>{{ number_format($product->getHPP()) }}</td>
          <td>{{ $product->minStock }}</td>
          <td>
            <a href="{{ route('inventory.edit', $product->productID) }}" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
          </td>
        </tr>
      @endforeach
    </tbody>
</table>
@endsection