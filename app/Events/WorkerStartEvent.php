<?php
namespace App\Events;

use DB;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Swoole\Http\Server;

class WorkerStartEvent implements WorkerStartInterface
{

    public function __construct()
    {
    }

    public function handle(Server $server, $workerId)
    {
        // TODO: Implement handle() method.
        DB::table('ws')->delete();
    }
}
