<?php namespace BadChoice\Grog\Services;

class ResourceRoute{

    protected $prefix = '';

    public static function modelClass($model){
        $method = explode("@", config('resourceRoute.namespaceResolver'));
        $action = $method[1];
        return $method[0]::$action($model);
    }

    public static function url_to_create(){
        return url(resourcePrefix('/') . '/create');
    }

    public static function url_to_index(){
        return url(resourcePrefix('/'));
    }

    public static function url_to_show($object){
        return url(resource_url($object) . '/' . $object->id);
    }

    public static function link_to_show($object, $title = null, $attributes = null){
        return link_to_route( object_route($object) . '.show', $title, ['id' => $object->id], $attributes);
    }

    public static function link_to_edit($object){
        return link_to_route( object_route($object) . '.edit', '', ["id" => $object->id], ["class" => "showPopup edit"]) ;
    }

    public static function link_to_delete($object){
        return link_to_route( object_route($object) . '.destroy', '',["id" => $object->id], ["class" => "delete-resource", "data-delete" => "confirm resource"]) ;
    }

    public static function route_to_update(){
        return resourceName() . '.update';
    }

    public static function route_to_store(){
        return resourceName(). '.store';
    }

    public static function resource_route($model, $separator = '.'){
        $namespace = collect(explode('\\',static::modelClass($model)))->slice(-2);
        return ltrim(collect([
            //rtrim(config('resourceRoute.admin_prefix'),'/'),      //Laravel 5.2
            //lcfirst($namespace->first()),                         //Laravel 5.2
            lcfirst(str_plural($namespace->last()))
        ])->implode($separator), '.');
    }

    public static function resource_url($object){
        if( is_string($object)){
            $namespace = collect(explode('\\',static::modelClass($object)))->slice(-2);
        }else {
            $namespace = collect(explode('\\', get_class($object)))->slice(-2);
        }
        return ltrim(collect([
            rtrim(config('resourceRoute.admin_prefix'), '/'),      //Laravel 5.2
            lcfirst($namespace->first()),                         //Laravel 5.2
            lcfirst(str_plural($namespace->last())),
        ])->implode("/"), '.');
    }

    public static function object_route($object, $separator = '.'){

        $namespace = collect(explode('\\',get_class($object)))->slice(-2);
        return ltrim(collect([
            //rtrim(config('resourceRoute.admin_prefix'),'/'),      //Laravel 5.2
            //lcfirst($namespace->first()),                         //Laravel 5.2
            lcfirst(str_plural($namespace->last())),
        ])->implode($separator),'.');
    }

    public static function resourceName(){
        //return collect(explode('.',request()->route()->getName()))->slice(0,-1)->last();
        return collect(explode('.',request()->route()->getName()))->slice(0,-1)->implode('.');
    }

    public static function resourcePrefix($separator = '.'){
        return collect(explode('/',request()->path()))->implode($separator);
        //return collect(explode('.',request()->route()->getName()))->slice(0,-1)->implode($separator); //Laravel 5.2
    }
}