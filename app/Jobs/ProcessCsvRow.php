<?php

namespace App\Jobs;

use App\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

use Illuminate\Support\Facades\Log;


class ProcessCsvRow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private $records;
    private $jobParent;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($record, $jobParent)
    {
        $this->records = $record;
        $this->jobParent = $jobParent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TransactionService $transactionService)
    {
        foreach($this->records as $record) {
            //hardcoded the account no. as not present in csv data
            $transactionService->import([
                'account_id' => 1,
                'label' => $record[0],
                'value' => $record[1],
                'date' => $record[2],
                'processed' => 0,
                'jobid' => $this->jobParent,
            ]);
        }
    }
}
