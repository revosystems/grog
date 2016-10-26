<?php

// RESTFul controllers helpers
function url_to_create(){
    return url(resourcePrefix('/').'/create');
}

function url_to_index(){
    return url(resourcePrefix('/'));
}

function link_to_show($object, $title = null, $attributes = null){
    return link_to_route(object_route($object) .'.show', $title,['id' => $object->id], $attributes);
}

function link_to_edit($object){
    return link_to_route( object_route($object) .'.edit','', ["id" => $object->id] , ["class" => "showPopup edit"]) ;
}

function link_to_delete($object){
    return link_to_route( object_route($object).'.destroy','',["id" => $object->id] , ["class" => "delete-resource"]) ;
}

function route_to_update(){
    return resourcePrefix() .'.update';
}

function route_to_store(){
    return resourcePrefix(). '.store';
}

//--------------------------------------------------------
function resource_route($model, $separator = '.'){
    $namespace = collect(explode('\\',Kernel::modelClass($model)))->slice(-2);
    return collect([
        rtrim(adminPrefix(),'/'),
        strtolower($namespace->first()),
        lcfirst(str_plural($namespace->last()))
    ])->implode($separator);
}

function object_route($object, $separator = '.'){
    $namespace = collect(explode('\\',get_class($object)))->slice(-2);
    return collect([
        rtrim(adminPrefix(),'/'),
        strtolower($namespace->first()),
        lcfirst(str_plural($namespace->last())),
    ])->implode($separator);
}
//--------------------------------------------------------
function resourceName(){
    return collect(explode('.',request()->route()->getName()))->slice(0,-1)->last();
}

function  resourcePrefix($separator = '.'){
    return collect(explode('.',request()->route()->getName()))->slice(0,-1)->implode($separator);
}
