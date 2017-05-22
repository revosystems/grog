<?php

namespace BadChoice\Grog\Services;


class SQLiteAccountCreator extends AccountCreator{

    protected function createDatabase(){
    }

    protected function rollback() {
        if ($this->user) $this->user->forceDelete();
    }
}