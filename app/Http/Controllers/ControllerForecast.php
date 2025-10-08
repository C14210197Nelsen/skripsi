<?php

namespace App\Http\Controllers;

use App\Models\ForecastJob;
use App\Jobs\RunForecastJob;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ControllerForecast extends Controller {
    private $pythonPath;

    public function __construct() {
        $this->pythonPath = "C:\\Users\\nelse\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    }

    public function getForecast($productID) {
        $output = ['status' => 'error'];

        try {
            // Cek status job di ForecastJob
            $job = ForecastJob::where('productID', $productID)
                ->orderByDesc('created_at')
                ->first();

            if ($job && in_array($job->status, ['Pending', 'Running'])) {
                // Jika masih training
                return response()->json([
                    'status' => 'success',
                    'productID' => (int) $productID,
                    'model' => [
                        'p' => null,
                        'd' => null,
                        'q' => null,
                        'mae' => null,
                        'mape' => null,
                        'avg_sales' => null,
                    ],
                    'forecast_next_2_months' => [],
                    'forecast_last_2_months' => [],
                    'chart' => [
                        'labels' => [],
                        'actual' => [],
                        'forecast' => [],
                    ],
                    'message' => 'Training model sedang berjalan...',
                    'isRunning' => true
                ]);
            }

            // Cek Model
            $model = \DB::table('sales_forecast')
                ->where('productID', $productID)
                ->orderByDesc('created_at')
                ->first();

            if (!$model) {
                return response()->json([
                    'status' => 'success',
                    'productID' => (int) $productID,
                    'model' => [
                        'p' => null,
                        'd' => null,
                        'q' => null,
                        'mae' => null,
                        'mape' => null,
                        'avg_sales' => null,
                    ],
                    'forecast_next_2_months' => [],
                    'forecast_last_2_months' => [],
                    'chart' => [
                        'labels' => [],
                        'actual' => [],
                        'forecast' => [],
                    ],
                    'message' => 'Jalankan "Change Model"',
                    'isRunning' => false
                ]);
            }


            // Hitung jumlah bulan forecast ke depan
            $leadtime = \DB::table('product')
                ->where('productID', $productID)
                ->value('leadtime');

            $forwardMonths = ($leadtime <= 60) ? 2 : ceil($leadtime / 30);

            // Hitung batas maksimal bulan forecast yang akan ditampilkan
            $maxForecastMonth = \Carbon\Carbon::now()->addMonths($forwardMonths)->format('Y-m');


            // Ambil data forecast
            $forecastRows = \DB::table('sales_forecast')
                ->where('productID', $productID)
                ->whereNotNull('forecast_quantity')
                ->whereRaw("DATE_FORMAT(forecast_month, '%Y-%m') <= ?", [$maxForecastMonth])
                ->orderBy('forecast_month')
                ->get();

            // Ambil data aktual
            $actualRows = \DB::table('salesorder as s')
                ->join('salesdetail as d', 's.salesID', '=', 'd.SalesOrder_salesID')
                ->selectRaw("DATE_FORMAT(s.salesDate, '%Y-%m') as bulan, SUM(d.quantity) as jumlah")
                ->where('d.Product_productID', $productID)
                ->where('s.status', 1)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->take(-12);

            $currentMonth = date('Y-m');

            $labels = $actualRows->pluck('bulan')
                ->map(fn($b) => substr($b, 0, 7)) // potong YYYY-MM dari actual
                ->merge(
                    $forecastRows->pluck('forecast_month')->map(fn($b) => substr($b, 0, 7)) // potong YYYY-MM dari forecast
                )
                ->unique()
                ->sort()
                ->values();


            $actual = [];
            $forecast = [];

            foreach ($labels as $bulan) {
                $act = $actualRows->firstWhere('bulan', $bulan);
                $for = $forecastRows->first(function ($r) use ($bulan) {
                    return substr($r->forecast_month, 0, 7) === $bulan;
                });

                // jika bulan actual > bulan saat ini, tampilkan null
                if ($bulan > date('Y-m')) {
                    $actual[] = null;
                } else {
                    $actual[] = $act ? (float) $act->jumlah : 0;
                }

                $forecast[] = $for ? (float) $for->forecast_quantity : null;
            }

            // Ambil backward & forward forecast saja
            $forecastLast = $forecastRows->sortBy('forecast_month')->take(3)->map(function ($r) {
                return [
                    'month' => $r->forecast_month,
                    'qty' => $r->forecast_quantity
                ];
            })->values();

            $forecastNext = $forecastRows->sortByDesc('forecast_month')->take($forwardMonths)->map(function ($r) {
                return [
                    'month' => $r->forecast_month,
                    'qty' => $r->forecast_quantity
                ];
            })->values();

            // Output akhir
            $output = [
                'status' => 'success',
                'productID' => (int) $productID,
                'model' => [
                    'p' => $model->p,
                    'd' => $model->d,
                    'q' => $model->q,
                    'mae' => $model->MAE,
                    'mape' => $model->MAPE,
                    'avg_sales' => $model->avg_sales,
                ],
                'forecast_next_2_months' => $forecastNext,
                'forecast_last_2_months' => $forecastLast,
                'chart' => [
                    'labels' => $labels,
                    'actual' => $actual,
                    'forecast' => $forecast,
                ],
                'message' => null,
                'isRunning' => false
            ];




        } catch (\Exception $e) {
            $output['message'] = $e->getMessage();
        }

        return response()->json($output);
    }



    // Change Model
    public function trainModel($productID) {
        $output = ['status' => 'error'];
        try {
            $process = new Process([$this->pythonPath, base_path('forecasting/train_model.py'), $productID]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $data = json_decode($process->getOutput(), true);
            $output = $data;
        } catch (\Exception $e) {
            $output['message'] = $e->getMessage();
        }

        return response()->json($output);
    }

    // Run Forecast
    public function runForecast($productID) {
        $output = ['status' => 'error'];
        try {
            $process = new Process([$this->pythonPath, base_path('forecasting/run_forecast.py'), $productID]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $data = json_decode($process->getOutput(), true);
            $output = $data;
        } catch (\Exception $e) {
            $output['message'] = $e->getMessage();
        }

        return response()->json($output);
    }

    public function requestForecast($productID) {
        $job = ForecastJob::create([
            'productID' => $productID,
            'status' => 'Pending',
        ]);

        RunForecastJob::dispatch($job->id);

        return response()->json([
            'status' => 'Queued',
            'message' => 'Forecast sedang diproses di background',
            'job_id' => $job->id
        ]);
    }

    // public function checkStatus($jobId)
    // {
    //     $job = ForecastJob::find($jobId);

    //     if (!$job) {
    //         return response()->json(['status' => 'not_found']);
    //     }

    //     return response()->json([
    //         'status' => $job->status,
    //         'message' => $job->message
    //     ]);
    // }

    public function checkStatus($jobId) {
        $job = \App\Models\ForecastJob::find($jobId);

        if (!$job) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Job tidak ditemukan.'
            ]);
        }

        $result = null;
        $hasResult = false;
        $hasError = false;

        if (!empty($job->message)) {
            $decoded = json_decode($job->message, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $result = $decoded;
                $hasResult = true;
                $hasError = isset($decoded['status']) && strtolower($decoded['status']) === 'error';
            }
        }

        return response()->json([
            'status'    => $job->status,        // 'Pending' | 'Running' | 'Done' | 'Error'
            'message'   => $job->message,      
            'result'    => $result,           
            'hasResult' => $hasResult,
            'hasError'  => $hasError,
            'productID' => (int) $job->productID,
        ]);
    }

}
