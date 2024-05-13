<?php

namespace BadChoice\Grog\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RVConnection {

    static ?string $provider;

    protected bool $useReportsDatabase   = false;
    protected string $databaseName;
    protected string $connectionName;
    protected ?string $dbInstance;

    public function __construct($database)
    {
        $this->databaseName = $database;
        $this->connectionName = $database;
    }

    public function setConnectionName($connectionName): self
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    public function useReportsDatabase($useReportsDatabase = true): self
    {
        $this->useReportsDatabase = $useReportsDatabase;
        return $this;
    }

    public function atInstance(?string $instanceName): self
    {
        $this->dbInstance = $instanceName;
        return $this;
    }

    protected function getDatabase()
    {
        return config('tenants.DB_TENANTS_PREFIX').$this->databaseName;
    }

    public function create(bool $shouldConnect = false): self
    {
        if (! $this->databaseName) {
            return $this;
        }

        $config = [
            ...config('database.connections.mysql'),
            'driver'    => $this->getDriver(),
            'database'  => $this->getDatabase(),
            'host'      => $this->getHost(),
            'port'      => $this->getPort(),
            'username'  => $this->getUsername(),
            'password'  => $this->getPassword(),
        ];

        $connection = "database.connections.{$this->connectionName}";
        $currentConfig = Config::get($connection);
        Config::set($connection, $config);
        if ($shouldConnect === true) {
            $this->disconnect($currentConfig);
            $this->connect();
        }

        return $this;
    }

    protected function getDriver() : string {
        return 'mysql';
    }

    public function connect()
    {
        DB::setDefaultConnection($this->connectionName);
    }

    public function disconnect(array $currentConfig = null): void
    {
        $currentConfig = $currentConfig ?? DB::connection($this->databaseName)?->getConfig();
        if (empty($currentConfig)) {
            return;
        }

        if ($currentConfig['host'] === $this->getHost() && $currentConfig['port'] === $this->getPort() && $currentConfig['username'] === $this->getUsername()) {
            return;
        }
        DB::disconnect($this->databaseName);
    }

    protected function getUsername()
    {
        return $this->useReportsDatabase ? config('tenants.DB_REPORTS_USERNAME') : config('tenants.DB_USERNAME');
    }

    protected function getHost()
    {
        if ($this->dbInstance) {
            return config("tenants.DB_INSTANCES.{$this->dbInstance}." . ($this->useReportsDatabase ? 'reports' : 'main'). '.host');
        }
        return $this->useReportsDatabase ? config('tenants.DB_REPORTS_HOST') : config('tenants.DB_HOST');
    }

    protected function getPort()
    {
        if ($this->dbInstance) {
            return config("tenants.DB_INSTANCES.{$this->dbInstance}." . ($this->useReportsDatabase ? 'reports' : 'main'). '.port');
        }
        return $this->useReportsDatabase ? config('tenants.DB_REPORTS_PORT') : config('tenants.DB_PORT');
    }

    protected function getPassword()
    {
        if ($this->dbInstance) {
            return config("tenants.DB_INSTANCES.{$this->dbInstance}." . ($this->useReportsDatabase ? 'reports' : 'main'). '.password');
        }
        return $this->useReportsDatabase ? config('tenants.DB_REPORTS_PASSWORD') : config('tenants.DB_PASSWORD');
    }
}
