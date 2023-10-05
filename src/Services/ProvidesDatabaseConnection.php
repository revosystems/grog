<?php

namespace BadChoice\Grog\Services;

interface ProvidesDatabaseConnection
{
    public function getDatabaseInstance() : ?string;
    public function getDatabaseName(): string;
    static function databaseConnectionProviderByName($string) : ?ProvidesDatabaseConnection;
}
