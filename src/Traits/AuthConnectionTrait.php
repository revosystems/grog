<?php namespace BadChoice\Grog\Traits;

trait AuthConnectionTrait{
    public function getConnectionName(){
        return config('tenants.DB_AUTH_CONNECTION');
    }
}