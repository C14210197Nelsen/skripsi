@extends('layouts.main')

@section('container')
  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Deleted Supplier</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
      <a href="{{ route('supplier.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>

<table class="table table-bordered">
    <thead CLASS= "table-dark text-center">
      <tr>
        <th>No</th>
        <th>Name</th>
        <th>Address</th>
        <th>Telephone</th>
        
        <th>Action</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      @foreach($suppliers as $supplier)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $supplier->supplierName }}</td>
          <td>{{ $supplier->address }}</td>
          <td>{{ $supplier->telephone }}</td>
          
          <td>
            <a href="{{ route('supplier.edit', $supplier->supplierID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
          </td>
        </tr>
      @endforeach
    </tbody>
</table>
@endsection