<?php namespace BadChoice\Grog\Traits;

use BadChoice\Grog\Services\AccountCreator;
use DB;
use Artisan;
use Storage;


trait TenantTrait{

    public static $avoidExtraMigrations = false;

    public static function newTenant($username, $password, $language = 'en', array $extraUserFields = [])
    {
        $accountCreator = app()->make( AccountCreator::class );
        return $accountCreator->with($username, $password, $language)->create($extraUserFields);
    }

    public static function doesUserExists($username){
        $user = static::where('username','=',$username)->first();
        return ($user != null);
    }

    public static function migrateAndSeed($username){
        static::migrate             ($username);
        static::seed                ($username);
        static::copyDefaultPhotos   ($username);
    }

    public static function migrate($username){
        foreach(config('tenants.migration_paths') as $path){
            Artisan::call('migrate'         ,['--database' => $username, '--path'     => $path,        '--force' =>true ]);
        }
        if (! TenantTrait::$avoidExtraMigrations)
            static::doExtraMigrations();
    }

    public static function seed($username){
        foreach(config('tenants.seed_classes') as $class) {
            Artisan::call('db:seed', ['--database' => $username, '--class' => $class, '--force' => true]);
        }
    }

    public static function rollback($username){
        foreach(config('tenants.migration_paths') as $path){
            Artisan::call('migrate:rollback'   ,array('--database' => $username, '--force' =>true));
        }
    }

    public static function copyDefaultPhotos($username){
        try {
            Artisan::queue('tenants:copyPhotos', ['origin' => 'baseTenant', 'destination' => $username]);
        }catch(\Exception $e){

        }
    }

    public static function deleteTenant($id){
        $user = static::find($id);
        if($user == null){
            echo "user doesn't exists";
            return false;
        }
        try {

            DB::setDefaultConnection('mysql');
            DB::statement('drop database '.config('tenants.DB_TENANTS_PREFIX') . $user->username . ';');

            $success = Storage::deleteDirectory($user->username);

            $user->forceDelete();
            return true;

        } catch (\Exception $e) {
            echo "Can't delete:" . $e->getMessage() . "<br>";
            return false;
        }
    }

    public static function toMigrate(){
        return static::all()->pluck('username');
    }

    public static function doExtraMigrations(){

    }
}