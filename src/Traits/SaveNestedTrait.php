<?php namespace BadChoice\Grog\Traits;

/**
 * Class SaveNestedTrait
 * @package BadChoice\Grog\Traits
 */
trait SaveNestedTrait
{
    public static function saveNested(array $nestedArray)
    {
        $toSaveNested = [];
        foreach ($nestedArray as $key => $value) {
            if (is_array($value)) {
                $toSaveNested[$key] = $value;
                unset($nestedArray[$key]);
            }
        }
        if ($nestedArray['id']) {
            $object = static::find($nestedArray['id']);
            $object->update($nestedArray);
        } else {
            $object = static::create($nestedArray);
        }

        foreach ($toSaveNested as $key => $array) {
            $relatedModel = $object->$key()->getRelated();
            $foreignKey   = $object->$key()->getPlainForeignKey();
            foreach($array as $content){
                $content->$foreignKey    = $object->id;
                $relatedModel::saveNested((array)$content);
            }
        }
        return $object;
    }
}