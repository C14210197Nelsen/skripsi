<?php
namespace App\Jobs;

use App\Models\ForecastJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class RunForecastJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobId;

    public function __construct($jobId) {
        $this->jobId = $jobId;
    }

    public function handle() {
        $forecastJob = ForecastJob::find($this->jobId);

        if (!$forecastJob) return;

        $forecastJob->update(['status' => 'Running']);

        try {
            $python = "C:\\Users\\nelse\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
            $script = base_path("forecasting/train_model.py");

            $process = new Process([$python, $script, $forecastJob->productID]);
            $process->setTimeout(3600);
            $process->run();

            if ($process->isSuccessful()) {
                $forecastJob->update([
                    'status' => 'Done',
                    'message' => $process->getOutput()
                ]);
            } else {
                $forecastJob->update([
                    'status' => 'Error',
                    'message' => $process->getErrorOutput()
                ]);
            }
        } catch (\Exception $e) {
            $forecastJob->update([
                'status' => 'Error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
