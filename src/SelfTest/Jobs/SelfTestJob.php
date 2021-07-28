<?php

namespace Jsantoso\LaravelServices\SelfTest\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SelfTestJob implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $key;
    protected $data;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($key, $data) {
        $this->key = $key;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        \Cache::put($this->key, $this->data, 60);
    }

}
