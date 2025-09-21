<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class ControllerCustomer extends Controller {
    
    public function index() {
        $customers = Customer::where('status', 1)->get();

        return view('customer.index', [
            'title' => 'Customer',
            'user' => 'Nama Pengguna',
            'customers' => $customers
        ]);
    }

    public function deleted() {
        $deletedCustomers = Customer::where('status', 0)->get();

        return view('customer.deleted', [
            'title' => 'Deleted Customer',
            'user' => 'Nama Pengguna',
            'customers' => $deletedCustomers
        ]);
    }

    
    public function create() {
        return view('customer.create', [
            'title' => 'Create Customer',
            'user' => 'Nama'
        ]);
    }
    
    
    public function store(Request $request) {
        $request->validate([
            'customerName' => 'required|max:32|unique:customer,customerName', 
            'address' => 'max:4000',
            'telephone' => 'max:16',
            'status' => 'required|boolean'
        ]);

        Customer::create($request->all());
    
        return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan!');
    }
    
    public function show(Customer $customer) {
        //
    }

    public function edit(Customer $customer) {
        return view('customer.edit', [
            'title' => 'Edit Customer',
            'user' => 'Nama Pengguna',
            'customer' => $customer
        ]);
    }


    public function update(Request $request, Customer $customer) {
        $request->validate([
            'customerName' => 'required|max:32|unique:customer,customerName,'  . $customer->customerName . ',customerName',
            'address' => 'max:4000',
            'telephone' => 'max:16',
            'status' => 'required|boolean'
        ]);

        $customer->update($request->all());

        return redirect()->route('customer.index')->with('success', 'Customer berhasil diperbarui!');
    }

    public function destroy(Customer $customer) {
        // Cek apakah ada sales terkait
        if ($customer->salesorders()->exists()) {
            $customer->update(['status' => 0]);

            return redirect()->route('customer.index')
                ->with('success', 'Customer memiliki data penjualan, status diubah menjadi Tidak Aktif.');
        }

        // Hapus jika tidak ada transaksi
        $customer->delete();

        return redirect()->route('customer.index')
            ->with('success', 'Customer berhasil dihapus!');
    }

}
