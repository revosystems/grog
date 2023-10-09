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

    public function setConnectionName($connectionName){
        $this->connectionName = $connectionName;
        return $this;
    }

    public function useReportsDatabase($useReportsDatabase = true)
    {
        $this->useReportsDatabase = $useReportsDatabase;
        return $this;
    }

    public function atInstance(?string $instanceName) {
        $this->dbInstance = $instanceName;
        return $this;
    }

    private function getDatabase(){
        $prefix = config('tenants.DB_TENANTS_PREFIX');
        return App::environment('testing') ? config('database.connections.'.config('database.default').'.database', ':memory:') : $prefix.$this->databaseName;
    }

    public function create($shouldConnect = false){
        if (! $this->databaseName) return;

        $config = array_merge(config('database.connections.mysql'),[
            'driver'    => App::environment('testing') ? 'sqlite' : 'mysql',
            'database'  => $this->getDatabase(),
            'host'      => $this->getHost(),
            'username'  => $this->getUsername(),
            'password'  => $this->getPassword(),
        ]);
        Config::set('database.connections.'.$this->connectionName, $config);

        if ($shouldConnect) {
            $this->disconnect();
            $this->connect();
        }
        return $this;
    }

    public function connect()
    {
        DB::setDefaultConnection($this->connectionName);
    }

    public function disconnect()
    {
        $connection = DB::connection($this->databaseName);
        if (! $connection || ($connection->getConfig('host') == $this->getHost() && $connection->getConfig('username') == $this->getUsername()) ) return;
        DB::disconnect($this->databaseName);
    }

    protected function getUsername() {
        return ($this->useReportsDatabase) ? config('tenants.DB_REPORTS_USERNAME') : config('tenants.DB_USERNAME');
    }

    protected function getHost() {
        if ($this->dbInstance) {
            return config('tenants.DB_INSTANCES.'.$this->dbInstance . '.' . ($this->useReportsDatabase ? 'reports' : 'main'));
        }
        return ($this->useReportsDatabase) ? config('tenants.DB_REPORTS_HOST') : config('tenants.DB_HOST');
    }

    protected function getPassword() {
        return ($this->useReportsDatabase) ? config('tenants.DB_REPORTS_PASSWORD') : config('tenants.DB_PASSWORD');
    }
}