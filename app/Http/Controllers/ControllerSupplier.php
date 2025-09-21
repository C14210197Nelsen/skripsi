<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class ControllerSupplier extends Controller {

    public function index() {
        $suppliers = Supplier::where('status', 1)->get();
        return view('supplier.index', [
            'title' => 'Supplier',
            'user' => 'Nama', // atau Auth::user()->name jika login
            'suppliers' => $suppliers
        ]);
    }

    public function deleted() {
        $deletedSuppliers = Supplier::where('status', 0)->get();

        return view('supplier.deleted', [
            'title' => 'Deleted Suppliers',
            'user' => 'Nama Pengguna',
            'suppliers' => $deletedSuppliers
        ]);
    }

    
    public function create() {
        return view('supplier.create', [
            'title' => 'Create Supplier',
            'user' => 'Nama Pengguna'
        ]);
    }
    
    
    public function store(Request $request) {
        $request->validate([
            'supplierName' => 'required|max:32|unique:supplier,supplierName',
            'address' => 'max:4000',
            'telephone' => 'max:16',
            'status' => 'required|boolean'
        ]);
        Supplier::create($request->all());
    
        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan!');
    }


    public function show(Supplier $supplier) {
        //
    }

    public function edit(Supplier $supplier) {
        return view('supplier.edit', [
            'title' => 'Edit Supplier',
            'user' => 'Nama Pengguna',
            'supplier' => $supplier
        ]);
    }


    public function update(Request $request, Supplier $supplier) {
        $request->validate([
            'supplierName' => 'required|max:32|unique:supplier,supplierName,'  . $supplier->supplierName . ',supplierName',
            'address' => 'max:4000',
            'telephone' => 'max:16',
            'status' => 'required|boolean'
        ]);

        $supplier->update($request->all());

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui!');
    }

    public function destroy(Supplier $supplier) {
        // Cek apakah ada sales terkait
        if ($supplier->purchaseorders()->exists()) {
            // Soft delete (ubah status)
            $supplier->update(['status' => 0]);

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier memiliki data penjualan, status diubah menjadi Tidak Aktif.');
        }

        // Hapus benar-benar jika tidak ada penjualan
        $supplier->delete();

        return redirect()->route('supplier.index')
            ->with('success', 'Supplier berhasil dihapus!');
    }

}
