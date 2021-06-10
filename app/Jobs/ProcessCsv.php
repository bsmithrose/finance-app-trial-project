<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use League\Csv\Reader;
use App\Jobs\ProcessCsvRow;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;
use App\Services\TransactionService;
use App\Events\CsvImportFinishedEvent;

use Illuminate\Support\Facades\Log;

class ProcessCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TransactionService $transactionService)
    {
        $jobId = $this->job->getJobId();
        $reader = Reader::createFromPath($this->file, 'r');
        $records = $reader->getRecords();
        $recordsArray = [];

        foreach($records as $record) {
            if($record[0] === 'Label') {
                continue;
            }
            $recordsArray[] = $record;
        }

        $chunked = array_chunk($recordsArray, 200);

        $jobArray = [];
        foreach ($chunked as $chunk) {
            $jobArray[] = new ProcessCsvRow($chunk, $jobId);
        }

        $batch = Bus::batch($jobArray)
            ->then(function (Batch $batch) {
            })
            ->catch(function (Batch $batch) {
                Log::info($batch);
            })
            ->finally(fn() =>
                CsvImportFinishedEvent::dispatch($jobId)
            )->dispatch();
    }
}
