<?php 

namespace BadChoice\Grog\Commands;

use Illuminate\Console\Command;

use DB;
use Artisan;
use Exception;

abstract class BaseTenantsCommand extends Command {

    //Add this to the signature '{--tenant= : Specify a tenant to just do it}';

    protected function preHandle(){
        return true;
    }

    protected function postHandle(){ }

    protected abstract function handleTenant($tenant);

    public function handle(){
        if( ! $this->preHandle() ){
            return $this->info(' !! Aborted !!');    
        }
        
        if( $this->option('tenant') != '' ){
            return $this->privateHandleTenant( $this->option('tenant') );
        }

        $this->handleAllTenants();

        $this->info('');
        return $this->postHandle();
    }

    private function handleAllTenants(){
        $class = config('tenants.user');                
        $users = $class::toMigrate();
        $bar   = $this->output->createProgressBar( $users->count() );

        $users->each(function($user) use($bar){
            $this->privateHandleTenant($user);
            $bar->advance();
            $bar->closeAllConnections();
        });
        $bar->finish();
    }

    private function privateHandleTenant($tenant){
        try {
            $this->handleTenant($tenant);
        }
        catch(\Exception $e){
            $this->error("[Error: {$tenant}]  " . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine() );
        }
    }

    private function closeAllConnections()
    {
        foreach (app('db')->getConnections() as $connection) {
            $connection->disconnect();
        }
    }

}
