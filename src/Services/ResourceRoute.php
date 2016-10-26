<?php namespace BadChoice\Grog\Services;

class ResourceRoute{

    protected $prefix = '';

    public static function modelClass(){
        return "";
    }

    public static function url_to_create(){
        return url(resourcePrefix('/').'/create');
    }

    public static function url_to_index(){
        return url(resourcePrefix('/'));
    }

    public static function link_to_show($object, $title = null, $attributes = null){
        return link_to_route( object_route($object) .'.show', $title,['id' => $object->id], $attributes);
    }

    public static function link_to_edit($object){
        return link_to_route( object_route($object) .'.edit','', ["id" => $object->id] , ["class" => "showPopup edit"]) ;
    }

    public static function link_to_delete($object){
        return link_to_route( object_route($object).'.destroy','',["id" => $object->id] , ["class" => "delete-resource"]) ;
    }

    public static function route_to_update(){
        return resourcePrefix() .'.update';
    }

    public static function route_to_store(){
        return resourcePrefix(). '.store';
    }

    public static function resource_route($model, $separator = '.'){
        $namespace = collect(explode('\\',static::modelClass($model)))->slice(-2);
        return collect([
            rtrim(config('resourceRoute.admin_prefix'),'/'),
            strtolower($namespace->first()),
            lcfirst(str_plural($namespace->last()))
        ])->implode($separator);
    }

    public static function object_route($object, $separator = '.'){
        $namespace = collect(explode('\\',get_class($object)))->slice(-2);
        return collect([
            rtrim(config('resourceRoute.admin_prefix'),'/'),
            strtolower($namespace->first()),
            strtolower($namespace->first()),
            lcfirst(str_plural($namespace->last())),
        ])->implode($separator);
    }

    public static function resourceName(){
        return collect(explode('.',request()->route()->getName()))->slice(0,-1)->last();
    }

    public static function  resourcePrefix($separator = '.'){
        return collect(explode('.',request()->route()->getName()))->slice(0,-1)->implode($separator);
    }
}