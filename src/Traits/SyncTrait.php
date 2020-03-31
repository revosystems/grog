<?php namespace BadChoice\Grog\Traits;

trait SyncTrait{

    /*
    |--------------------------------------------------------------------------
    | SYNC FILTER
    |--------------------------------------------------------------------------
    |
    | This function will be used to filter the models to sync
    |
    */
    public static function syncFilter($query){
        return $query;
    }


    public static function shouldSync($fromDate){
        if($fromDate == '') return '1';
        $instance    = new static;
        $shouldSync  = static::syncFilter($instance->newQuery()->withTrashed()->where('updated_at', '>', $fromDate)->select('id'))->first();
        return ($shouldSync != null) ? "1" : "0";
    }

    public static function sync($fromDate = '', $skip = null, $take = null){
        if ($fromDate === '') {
            $fromDate = date("Y-m-d H:i:s",0);
        }
        $instance    = new static;
        $newQuery    = static::getNewSyncQuery($fromDate, $instance);
        $updateQuery = static::getUpdatedSyncQuery($fromDate, $instance);
        $deleteQuery = static::getDeletedSyncQuery($fromDate, $instance);

        if ($skip !== null && $take !== null) {
            $newQuery    = $newQuery->skip($skip)->take($take);
            $updateQuery = $updateQuery->skip($skip)->take($take);
            $deleteQuery = $deleteQuery->skip($skip)->take($take);
        }

        if(strtotime($fromDate == 0)){
            return array(
                'new'       => $newQuery->get()->toArray(),
                'updated'   => null,
                'deleted'   => null,
            );
        }

        return array(
            'new'       => $newQuery    ->get()->toArray(),
            'updated'   => $updateQuery ->get()->toArray(),
            'deleted'   => $deleteQuery ->get()->toArray(),
        );
    }

    protected static function getNewSyncQuery($fromDate, $instance)
    {
        return static::syncFilter($instance->newQuery()->where(function($query) use (&$fromDate){
            $query->where('created_at','>',$fromDate)->orWhereNull('created_at');
        }));
    }

    protected static function getDeletedSyncQuery($fromDate, $instance)
    {
        return static::syncFilter($instance->newQuery()->onlyTrashed()->where('deleted_at', '>', $fromDate));
    }

    protected static function getUpdatedSyncQuery($fromDate, $instance)
    {
        return static::syncFilter($instance->newQuery()->where('updated_at', '>', $fromDate)->where('created_at', '<', $fromDate));
    }
}