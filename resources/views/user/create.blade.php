@extends('layouts.main')

@section('container')
<div class="container">
  <h2>Create User</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif  

  <form action="{{ route('user.store') }}" method="POST">
    @csrf

    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <div class="mb-3">
      <label for="fullName" class="form-label">Full Name</label>
      <input type="text" class="form-control" id="fullName" name="fullName" value="{{ old('fullName') }}" required>
    </div>

    <div class="mb-3">
      <label for="role" class="form-label">Role</label>
      <select class="form-select" id="role" name="role" required>
        @foreach(\App\Models\Userlogin::ROLE_OPTIONS as $role)
          <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
            {{ ucfirst($role) }}
          </option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Save</button>
    <a href="{{ route('user.index') }}" class="btn btn-secondary">Discard</a>
  </form>
</div>
@endsection
