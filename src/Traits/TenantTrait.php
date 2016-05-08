<?php namespace BadChoice\Grog\Traits;

use DB;
use Artisan;
use Storage;
use App;
use Hash;

trait TenantTrait{

    public static function newTenant($username, $password, $language = 'en', array $extraUserFields)
    {
        $username = preg_replace("/[^a-z0-9]+/", "", strtolower($username));
        App::setLocale($language);
        try {
            DB::statement('create database '. config('tenants.DB_TENANTS_PREFIX') . $username . ';');
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
        } catch (\Exception $e) {
            DB::statement('drop database '.config('tenants.DB_TENANTS_PREFIX') . $username . ';');
            $user->forceDelete();
            echo "<br>Something went wrong..." . $e->getMessage();
            return null;
        }
    }

    public static function migrateAndSeed($username){
        static::migrate($username);
        static::seed($username);
        //User::copyDefaultPhotos($username);
    }

    public static function migrate($username){
        //Artisan::call('migrate:install' ,['--database' => 'tenant_'.$user ]);
        //Artisan::call('migrate'         ,['--database' => $username, '--path'     => 'database/migrations/tenants',        '--force' =>true ]);
        //Artisan::call('migrate'         ,['--database' => $username, '--path'     => 'database/migrations/tenants/stocks', '--force' =>true ]);
        foreach(config('tenants.migration_paths') as $path){
            Artisan::call('migrate'         ,['--database' => $username, '--path'     => $path,        '--force' =>true ]);
        }

    }

    public static function seed($username){
        foreach(config('tenants.seed_classes') as $class) {
            Artisan::call('db:seed', ['--database' => $username, '--class' => $class, '--force' => true]);
        }
        /*        Artisan::call('db:seed', ['--database' => $username, '--class' => 'TenantConfigSeeder', '--force' => true]);
                Artisan::call('db:seed'         ,['--database' => $username, '--class'    => 'TenantProductsSeeder'        ,'--force' =>true]);*/
        /*Artisan::call('db:seed'       ,['--database' => $username, '--class'    => 'TenantSeederConfiguration' ,'--force' =>true]);
        Artisan::call('db:seed'         ,['--database' => $username, '--class'    => 'TenantSeederTables'        ,'--force' =>true]);*/
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
}