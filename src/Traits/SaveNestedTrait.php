<?php namespace BadChoice\Grog\Traits;

/**
 * Each model can then implement the saveKeyNested(array) where `Key` is the variable and
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
            $method = "save" . ucfirst($key) . "Nested";
            if (method_exists($object, $method)) {
                $object->$method($array);
            }
        }
        return $object;
    }
}