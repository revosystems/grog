<?php
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * Creates a connection for the database of the $user
 * the name of the connection will be 'RevoRetail_{$user}'
 * This doesn't check if the database exists
 *
 * @param $user
 * @param $shouldConnect
 * @param bool $reports true to connect to the read only database insatance
 */
function createDBConnection($user, $shouldConnect = false, $reports = false) {

    $prefix         = config('tenants.DB_TENANTS_PREFIX');
    $database       = $prefix.$user;
    $host           = ($reports) ? config('tenants.DB_REPORTS_HOST') : config('tenants.DB_HOST');
    $username       = config('tenants.DB_USERNAME');
    $password       = config('tenants.DB_PASSWORD');
    $tablesPrefix   = config('tenants.DB_TABLES_PREFIX');

    Config::set('database.connections.'.$user, [
        'driver'    => App::environment('testing') ? 'sqlite' : 'mysql',
        'host'      => $host,
        'database'  => App::environment('testing') ? ':memory:' : $database,
        'username'  => $username,
        'password'  => $password,
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => $tablesPrefix,
    ]);

    if($shouldConnect){
        DB::setDefaultConnection($user);
    }
}

/**
 * Creates an array that can be the input of Html::configForm('select'...) from a collection
 *
 * @param $items the collection to create the array
 * @param bool $canBeNull set true if you want to allow a null value
 * @param string $fieldName by default uses name field but you can specify another one
 * @return array
 */
function createSelectArray($items, $canBeNull = false ,$fieldName = 'name'){
    $selectArray = array();
    if($canBeNull){
        $selectArray[''] = '--';
    }
    foreach($items as $item){
        $selectArray[$item->id] = $item->$fieldName;
    }
    return $selectArray;
}


/**
 * Replaces empty strings for null of an array (useful after the Html::configForm('select'...) when selecting the null value)
 * @param $array
 * @return mixed
 */
function setNullOnEmptyStrings($array){
    foreach($array as $key => $value){
        if($value == "") $array[$key] = null;
    }
    return $array;
}

/**
 * Prints the number with the currency format
 */
function currencyNumber($number, $currency){
    $symbol = $symbol = currencySymbol($currency);
    if($currency == 'USD' || $currency == 'GBP')    { return $symbol . ' ' . number_format($number, 2);    }
    else                                            { return commaNumber($number, true) . ' ' . $symbol;   }
}

function currencySymbol($currency = "EUR"){
    $currencySymbols    = include base_path().'/resources/currencies/currencies_symbols.php';
    return $currencySymbols[$currency];
}

function commaNumber($number,$decimals = true){
    if($decimals) return number_format($number,2,',','.');
    else          return number_format($number,0,',','.');
}

function shrinkText($string, $max = 12){
    if(strlen($string) > ($max -3)) {
        return substr($string, 0, $max-3) . '...';
    }
    return $string;
}

//===============================================================
// TIMEZONE
//===============================================================
function timezone_list() {
    static $timezones = null;

    if ($timezones === null) {
        $timezones = [];
        $offsets = [];
        $now = new DateTime();

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $now->setTimezone(new DateTimeZone($timezone));
            $offsets[] = $offset = $now->getOffset();
            $timezones[$timezone] = '(' . format_GMT_offset($offset) . ') ' . format_timezone_name($timezone);
        }

        array_multisort($offsets, $timezones);
    }
    return $timezones;
}

function format_GMT_offset($offset) {
    $hours = intval($offset / 3600);
    $minutes = abs(intval($offset % 3600 / 60));
    return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
}

function format_timezone_name($name) {
    $name = str_replace('/', ', ', $name);
    $name = str_replace('_', ' ', $name);
    $name = str_replace('St ', 'St. ', $name);
    return $name;
}

function measureBlock($callback, $title = "Measure"){
    $start = microtime(true);
    $callback();
    $end    = microtime(true);
    $diff   = $end - $start;

    echo $title .": " . $diff;
}

function nameOrDash($object){
    if($object) return $object->name;
    return "--";
}

function icon($icon) {
    return FA::icon($icon);
}