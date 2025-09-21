@extends('layouts.main')

@section('container')

<div class="container">
  <h2>Movement - {{ $product->productName }} ({{ $product->productCode }})</h2>

  <table class="table table-bordered table-index">
    <thead class="table-dark text-center">
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Quantity</th>
        <th>Updated Stock</th>
        <th>Cost</th>
        <th>Total</th>
        <th>Source</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      @forelse($ledgers as $ledger)
        <tr>
          <td>{{ \Carbon\Carbon::parse($ledger->created_at)->format('d M Y H:i') }}</td>
          <td>{{ ucfirst($ledger->type) }}</td>
          <td>{{ $ledger->qty }}</td>
          <td>{{ $ledger->saldo_qty }}</td>
          <td>{{ number_format($ledger->hpp) }}</td>
          <td>{{ number_format($ledger->saldo_harga) }}</td>
          <td>{{ $ledger->source_type }} #{{ $ledger->source_id }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7">No Movement.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

    <div class="circle-pagination mt-4">
      {{ $ledgers->links('pagination::bootstrap-5') }}
    </div>

    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('inventory.index') }}" class="btn btn-outline-danger">← Back</a>
    </div>

</div>
@endsection
