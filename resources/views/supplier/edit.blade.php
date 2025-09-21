@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Edit Supplier</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif  

  <form action="{{ route('supplier.update', $supplier->supplierID) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label for="supplierName" class="form-label">Supplier Name</label>
      <input type="text" class="form-control" id="supplierName" name="supplierName"
        value="{{ old('supplierName', $supplier->supplierName) }}" required>
    </div>

    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <textarea class="form-control" id="address" name="address" >{{ old('address', $supplier->address) }}</textarea>
    </div>

    <div class="mb-3">
      <label for="telephone" class="form-label">Telephone</label>
      <input type="text" class="form-control" id="telephone" name="telephone"
        value="{{ old('telephone', $supplier->telephone) }}">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" {{ $supplier->status == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ $supplier->status == 0 ? 'selected' : '' }}>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Update</button>
    <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
