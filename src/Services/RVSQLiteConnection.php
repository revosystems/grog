<?php

namespace BadChoice\Grog\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RVSQLiteConnection extends RVConnection {

    protected function getDatabase(){
        return storage_path('testing/'.$this->databaseName . '.sqlite');
    }

    protected function getDriver() : string {
        return 'sqlite';
    }
}
