<?php 

namespace BadChoice\Grog\Commands;

use Illuminate\Console\Command;

use DB;
use Artisan;
use Exception;

class TenantsMigrate extends BaseTenantsCommand
{
    protected $signature = 'tenants:migrate
                                {--rollback : Rollback all tenants or tenant selected}
                                {--tenant= : Specify a tenant to migrate (or rollback)}';

    protected $description = 'Migrate all tenants';

    protected function preHandle()
    {
        $this->info('');
        $this->info(' ===== TENANTS MIGRATION =====');
        $this->info('');

        if ($this->option('rollback')) {
            $this->error('*******************************');
            $this->error('**       ROLLING BACK        **');
            $this->error('*******************************');
            $this->info('');
        }
        return $this->confirm('Do you wish to continue?', false);
    }

    protected function postHandle()
    {
        $this->info('Dump autoload');
        exec('composer dump-autoload');
        Artisan::call('optimize');
        Artisan::call('queue:restart'); //To reload the code for queues (on daemon)
    }

    protected function handleTenant($tenant)
    {
        $class = config('tenants.user');
        createDBConnection($tenant);
        if ($this->option('rollback')) {
            return $class::rollback($tenant);
        }
        $class::migrate($tenant);
        $this->closeAllConnections();   //To avoid the `too many connections exception`
    }

    private function closeAllConnections()
    {
        foreach (app('db')->getConnections() as $connection) {
            $connection->disconnect();
        }
    }
}
