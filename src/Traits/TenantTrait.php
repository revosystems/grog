<?php namespace BadChoice\Grog\Traits;

use DB;
use Artisan;
use Storage;
use App;
use Hash;

trait TenantTrait{

    public static function newTenant($username, $password, $language = 'en', array $extraUserFields)
    {
        $username       = preg_replace("/[^a-z0-9]+/", "", strtolower($username));
        if(static::doesUserExists($username)){
            throw new \Exception("Username already exists");
        }
        $databaseName   = config('tenants.DB_TENANTS_PREFIX') . $username;
        App::setLocale($language);

        try {
            DB::statement('create database '. $databaseName . ';');
            createDBConnection($username);
            $newArray = [
                'username'      => $username,
                'password'      => Hash::make($password),
                'appPassword'   => Hash::make($password),
                'language'      => $language,
            ];
            $user = static::create(array_merge($newArray,$extraUserFields));
            static::migrateAndSeed($username);
            return $user;
        }
        catch (\Exception $e) {
            DB::statement('drop database '. $databaseName . ';');
            if($user) $user->forceDelete();
            throw $e;
        }
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

    public static function copyDefaultPhotos($user){
        //Artisan::queue('revo:copyPhotos', ['origin' => 'baseTenant', 'destination' => $tenant ]);
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