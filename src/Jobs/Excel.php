<?php

namespace LaravelEnso\Tables\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config as ConfigFacade;
use LaravelEnso\Tables\Exports\Excel as Service;
use LaravelEnso\Tables\Services\Data\Config;

class Excel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout;

    protected User $user;
    protected Config $config;
    protected string $table;

    public function __construct(User $user, Config $config, string $table)
    {
        $this->user = $user;
        $this->config = $config;
        $this->table = $table;

        $this->timeout = ConfigFacade::get('enso.tables.export.timeout');
        $this->queue = ConfigFacade::get('enso.tables.queues.exports');
    }

    public function handle()
    {
        (new Service($this->user, $this->table(), $this->config))->handle();
    }

    protected function table()
    {
        return App::make($this->table, ['request' => $this->config->request()]);
    }
}
