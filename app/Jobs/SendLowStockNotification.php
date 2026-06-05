<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLowStockNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
   public  Item $item;
    /**
     * Create a new job instance.
     */
    public function __construct(Item $item)
    {
        $this->item=item;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
