<?php namespace BadChoice\Grog\Traits;

trait AuthConnectionTrait{
    public function getConnectionName(){
        return env('DB_AUTH_CONNECTION','mysql');
    }
}