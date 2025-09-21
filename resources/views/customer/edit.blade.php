@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Edit Customer</h2>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif  

  <form action="{{ route('customer.update', $customer->customerID) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="customerName" class="form-label">Customer Name</label>
      <input type="text" class="form-control" id="customerName" name="customerName" value="{{ old('customerName', $customer->customerName) }}" required>
    </div>

    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <textarea class="form-control" id="address" name="address">{{ old('address', $customer->address) }}</textarea>
    </div>

    <div class="mb-3">
      <label for="telephone" class="form-label">Telephone</label>
      <input type="text" class="form-control" id="telephone" name="telephone" value="{{ old('telephone', $customer->telephone) }}">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status">
        <option value="1" {{ $customer->status == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ $customer->status == 0 ? 'selected' : '' }}>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Update</button>
    <a href="{{ route('customer.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
