<?php namespace BadChoice\Grog\Commands;

use Illuminate\Console\Command;

use DB;
use Artisan;
use Exception;

class TenantsMigrate extends Command {

    protected $signature = 'tenants:migrate
	                            {--rollback : Rollback all tenants or tenant selected}
	                            {--tenant= : Specify a tenant to migrate (or rollback)}';

    protected $description = 'Migrate all tenants';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
        $this->info('');
        $this->info(' ===== TENANTS MIGRATION =====');
        $this->info('');

        if($this->option('rollback')){
            $this->error('*******************************');
            $this->error('**       ROLLING BACK        **');
            $this->error('*******************************');
            $this->info('');
        }

        if ($this->confirm('Do you wish to continue?',false))
        {
            //----------
            // SINGLE MIGRATE/ROLLBACK
            //----------
            if($this->option('tenant') != ''){
                $tenantName = $this->option('tenant');
                $this->info('Doing: '. $tenantName);
                $this->migrate($tenantName, $this->option('rollback'));
            }
            //----------
            // FULL MIGRATE/ROLLBACK
            //----------
            else {
                $this->info('');
                $class = config('tenants.user');
                $users = $class::toMigrate();

                $total = count($users);
                $i = 0;
                foreach ($users as $user) {
                    $i++;
                    $this->info('[' . number_format(($i / $total) * 100 ,2). '% ]  Doing ' . $user . '...');
                    $this->migrate($user, $this->option('rollback'));
                }
            }
            $this->info('');
            $this->info('Dump autoload');

            exec('composer dump-autoload');
            Artisan::call('optimize');
            Artisan::call('queue:restart'); //To reload the code for queues (on daemon)
        }
        else{
            $this->info('');
            $this->info(' !! Migration aborted !!');
        }
        $this->info('');
    }

    private function migrate($user,$rollback = false){
        $class = config('tenants.user');
        try{
            if($rollback){
                createDBConnection($user);
                $class::rollback($user);
            }
            else {
                createDBConnection($user);
                $class::migrate($user);
                //Custom raw
                //DB::statement("ALTER TABLE GS_businesses ADD printInvoice INT UNSIGNED NOT NULL DEFAULT '0'");
            }
        }
        catch(\Exception $e){
            $this->error('[Error] ' . $e->getMessage());
        }
    }
}
