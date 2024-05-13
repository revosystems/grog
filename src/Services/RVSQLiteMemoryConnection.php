<?php

namespace BadChoice\Grog\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RVSQLiteMemoryConnection extends RVConnection {

    protected function getDatabase(){
        return config('database.connections.'.config('database.default').'.database', ':memory:');
    }

    protected function getDriver() : string {
        return 'sqlite';
    }

    public function disconnect(array $currentConfig = null): void {
        return;
    }
}
