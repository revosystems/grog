<?php namespace BadChoice\Grog\Traits;

/**
 * SaveContentsNested removes the arrays inside arrays and calls the saveKeyNested(array) for each of those arrays
 * Each model can then implement the saveKeyNested(array) where `Key` is the array key
 * This function is useful to set the parent_id or do any other prior transformation, as well as calling again the saveNested to perform the actual create/update
 * Example:
 *     public function saveContentsNested($array){
 *          foreach($array as $content){
 *              $content->order_id = $this->id;
 *              OrderContent::saveNested((array)$content);
 *          }
 *      }
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