<?php

namespace BadChoice\Grog\Traits;

trait AuthConnectionTrait
{
    public function getConnectionName()
    {
        return config('tenants.db.connection');
    }
}
