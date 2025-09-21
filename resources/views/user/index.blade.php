@extends('layouts.main')

@section('title', 'User Management')

@section('container')
<div class="container mt-4">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">User Management</h2>
    <a href="{{ route('user.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
  </div>

  {{-- Tabel User --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th>Username</th>
          <th>Full Name</th>
          <th>Role</th>
          <th style="width: 18%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($userlogins as $u)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $u->username }}</td>
          <td>{{ $u->fullName }}</td>
          <td>{{ $u->role }}</td>
          <td class="d-flex justify-content-center gap-1 flex-wrap">
            <a href="{{ route('user.edit', $u->userID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
            <form action="{{ route('user.destroy', $u->userID) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmation to Delete')">
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
