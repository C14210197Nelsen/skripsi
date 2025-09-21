<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ShopeeSubmitImport;
use Maatwebsite\Excel\Facades\Excel;

class ControllerSalesImport extends Controller {
    public function submitShopee(Request $request) {
        ini_set('max_execution_time', 60000);
        ini_set('memory_limit', '4096M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $import = new ShopeeSubmitImport();
        Excel::import($import, $request->file('excel_file'));

        if (!empty($import->failedDetails)) {
            return redirect()->route('sales.index')
                ->with('warning', 'Beberapa pesanan gagal diimpor:<br>' . implode('<br>', $import->failedDetails));
        }

        return redirect()->route('sales.index')
            ->with('success', 'Semua pesanan berhasil diimpor!');
    }
}
