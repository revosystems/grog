<?php

namespace BadChoice\Grog\Services;

use DB;
use BadChoice\Grog\Exceptions\CreateAccountException;
use Illuminate\Support\Facades\App;

class AccountCreator{

    protected $username;
    protected $password;
    protected $language;
    protected $user;
    protected $databaseName;
    protected $shouldSeed = true;

    public function with($username, $password, $language){
        $this->username = preg_replace("/[^a-z0-9]+/", "", strtolower($username));;
        $this->password = $password;
        $this->language = $language ? : 'en';
        return $this;
    }

    public function shouldSeed($shouldSeed) {
        $this->shouldSeed = $shouldSeed;
        return $this;
    }

    public function create( $extraFields = []){
        if ($this->doesUserExists()){
            throw new CreateAccountException("Username already exists");
        }

        if( ! $this->password || strlen($this->password) < 4){
            throw new CreateAccountException("Password to weak");
        }

        if (App::runningUnitTests()) {
            $this->createUser($extraFields);
            return $this->user;
        }

        try {
            $this->createDatabase();
            $this->createUser($extraFields);
            $this->migrateAndSeed();
        }catch(\Exception $e){
            $this->rollback();
            throw new CreateAccountException($e->getMessage());
        }
        return $this->user;
    }

    protected function createDatabase(){
        $this->databaseName   = config('tenants.DB_TENANTS_PREFIX') . $this->username;
        DB::statement('create database '. $this->databaseName . ';');
    }

    protected function createUser($extraFields){
        $newArray = [
            'username'      => $this->username,
            'password'      => bcrypt($this->password),
            'appPassword'   => bcrypt($this->password),
            'language'      => $this->language,
        ];
        $userClass  = config('tenants.user');
        $userClass::$avoidExtraMigrations = true;
        $this->user = $userClass::create(array_merge( $newArray, $extraFields) );
    }

    protected function migrateAndSeed(){
        App::setLocale($this->language);
        createDBConnection($this->username);
        $userClass  = config('tenants.user');
        if ($this->shouldSeed) {
            return $userClass::migrateAndSeed($this->username);
        }
        $userClass::migrate($this->username);
    }

    protected function rollback(){
        DB::statement('drop database '. $this->databaseName . ';');
        if($this->user) $this->user->forceDelete();
    }

    public function doesUserExists(){
        $userClass  = config('tenants.user');
        return $userClass::doesUserExists($this->username);
    }
}
