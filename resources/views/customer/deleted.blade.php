@extends('layouts.main')

@section('container')

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Deleted Customer</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
      <a href="{{ route('customer.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
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
      @foreach($customers as $customer)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $customer->customerName }}</td>
          <td>{{ $customer->address }}</td>
          <td>{{ $customer->telephone }}</td>
        
          <td>
            <a href="{{ route('customer.edit', $customer->customerID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>          </td>
        </tr>
      @endforeach
    </tbody>
</table>
  
@endsection