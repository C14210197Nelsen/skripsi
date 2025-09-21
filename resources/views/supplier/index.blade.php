@extends('layouts.main')

@section('title', 'Supplier')

@section('container')
<div class="container mt-4">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Supplier</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('supplier.deleted') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Deleted</a>
      <a href="{{ route('supplier.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>

  {{-- Tabel --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th>Name</th>
          <th>Address</th>
          <th>Telephone</th>
          <th style="width: 18%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($suppliers as $supplier)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $supplier->supplierName }}</td>
          <td>{{ $supplier->address }}</td>
          <td>{{ $supplier->telephone }}</td>
          <td class="d-flex justify-content-center gap-1 flex-wrap">
            <a href="{{ route('supplier.edit', $supplier->supplierID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
            <form action="{{ route('supplier.destroy', $supplier->supplierID) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmation to Delete')">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

</div>
@endsection
