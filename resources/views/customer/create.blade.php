@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Create Customer</h2>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif  

  <form action="{{ route('customer.store') }}" method="POST">
    @csrf
    

    <div class="mb-3">
      <label for="customerName" class="form-label">Customer Name</label>
      <input type="text" class="form-control" id="customerName" name="customerName" value="{{ old('customerName') }}" required>
    </div>

    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <textarea class="form-control" id="address" name="address">{{ old('address') }}</textarea>
    </div>

    <div class="mb-3">
      <label for="telephone" class="form-label">Telephone</label>
      <input type="text" class="form-control" id="telephone" name="telephone" value="{{ old('telephone') }}">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Save</button>
    <a href="{{ route('customer.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
