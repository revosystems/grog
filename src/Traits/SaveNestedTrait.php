<?php

namespace BadChoice\Grog\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class SaveNestedTrait
 * @package BadChoice\Grog\Traits
 */
trait SaveNestedTrait
{
    public static function saveNested(array $nestedArray, bool $createIfNotFound = false)
    {
        $toSaveNested = [];
        foreach ($nestedArray as $relationMethodName => $value) {
            if (static::isARelationMethod($relationMethodName)) {
                if ($value != null) {
                    $toSaveNested[$relationMethodName] = $value;
                }
                unset($nestedArray[$relationMethodName]);
            }
        }
        if (isset($nestedArray['id']) && $nestedArray['id']) {
            if ($object = static::find($nestedArray['id'])) {
                $object->update($nestedArray);
            } else if ($createIfNotFound) {
                $object = static::create($nestedArray);
            }
        } else {
            $object = static::create($nestedArray);
        }

        foreach ($toSaveNested as $relationMethodName => $contents) {
            $relation = $object->$relationMethodName();
            $relatedModel = $relation->getRelated();
            $foreignKey = $relation->getForeignKeyName();
            $foreignKeyType = $relation instanceof MorphOneOrMany
                ? $relation->getMorphType()
                : null;

            $contents = is_array($contents) ? $contents : [$contents];

            if (static::isAnOverwritableMethodRelation($relationMethodName)) {
                $object->$relationMethodName()->delete();
            }
            foreach ($contents as $content) {
                $content->$foreignKey = $object->id;
                if ($foreignKeyType) {
                    $content->$foreignKeyType = get_class($object);
                }
                $relatedModel::saveNested((array) $content, $createIfNotFound);
            }
        }
        return $object;
    }

    public static function isARelationMethod(string $relationMethodName): bool
    {
        $object = new static();
        return  method_exists($object, $relationMethodName) && ($object->$relationMethodName() instanceof Relation);
    }

    public static function isAnOverwritableMethodRelation(string $relationMethodName): bool
    {
        $object = new static();
        return in_array($relationMethodName, $object->overwritableRelations ?? []);
    }
}