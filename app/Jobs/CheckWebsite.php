<?php

namespace App\Jobs;

use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $site;
    public $elapsedTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = $this->measureTime(fn() => Http::get($this->site->url));
        } catch (ConnectionException $exception) {
            $this->site->update(['is_resolving' => false, 'is_online' => false]);
            return;
        }
        $check = $this->site->checks()->create([
            'response_status' => $response->status(),
            'response_content' => $response->body(),
            'elapsed_time' => $this->elapsedTime
        ]);
        $this->site->update([
            'is_online' => $check->successful(),
            'is_resolving' => true
        ]);
    }

    protected function measureTime($closure)
    {
        $starTime = microtime(true);
        return tap($closure(), function () use ($starTime) {
            $endTime = microtime(true);
            $elapsedTime = ($endTime - $starTime) * 1000;
            $this->elapsedTime = (int) $elapsedTime;
        });
    }
}
