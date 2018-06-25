<?php namespace BadChoice\Grog\Traits;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class SaveNestedTrait
 * @package BadChoice\Grog\Traits
 */
trait SaveNestedTrait{

    public static function saveNested(array $nestedArray, $createIfNotFound = false)
    {
        $toSaveNested = [];
        foreach ($nestedArray as $key => $value) {
            if (static::isRelation($key) ) {
                if($value != null) {
                    $toSaveNested[$key] = $value;
                }
                unset($nestedArray[$key]);
            }
        }
        if (isset($nestedArray['id']) && $nestedArray['id']) {
            $object = static::find($nestedArray['id']);
            if($object)                 $object->update($nestedArray);
            else if($createIfNotFound)  $object = static::create($nestedArray);
        } else {
            $object = static::create($nestedArray);
        }

        foreach ($toSaveNested as $key => $array) {
            $relatedModel = $object->$key()->getRelated();
            $foreignKey   = $object->$key()->getForeignKeyName();


            if(is_array($array)) {
                foreach ($array as $content) {
                    $content->$foreignKey = $object->id;
                    $relatedModel::saveNested((array)$content, $createIfNotFound);
                }
            }
            else{
                $array->$foreignKey = $object->id;
                $relatedModel::saveNested((array)$array, $createIfNotFound);
            }
        }
        return $object;
    }

    public static function isRelation($key){
        $object = new static();
        return  method_exists( $object, $key) && ($object->$key() instanceof Relation);
    }
}