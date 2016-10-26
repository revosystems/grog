<?php use BadChoice\Grog\Services\ResourceRoute;

function url_to_create(){
    return ResourceRoute::url_to_create();
}

function url_to_index(){
    return ResourceRoute::url_to_index();
}

function link_to_show($object, $title = null, $attributes = null){
    return ResourceRoute::link_to_show($object,$title,$attributes);
}

function link_to_edit($object){
    return ResourceRoute::link_to_edit($object);
}

function link_to_delete($object){
    return ResourceRoute::link_to_delete($object);
}

function route_to_update(){
    return ResourceRoute::route_to_update();
}

function route_to_store(){
    return ResourceRoute::route_to_store();
}

function resource_route($model, $separator = '.'){
    ResourceRoute::resource_route($model,$separator);
}

function object_route($object, $separator = '.'){
    ResourceRoute::object_route($object,$separator);
}

function resourceName(){
    ResourceRoute::resourceName();
}

function  resourcePrefix($separator = '.'){
    ResourceRoute::resourcePrefix($separator);
}
