<?php

namespace Haxibiao\Live\Traits;

trait Macroable
{
    use \Illuminate\Support\Traits\Macroable {
        __call as macroCall;
    }

    public function getRelationValue($key)
    {
        $relation = parent::getRelationValue($key);
        if (! $relation && static::hasMacro($key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return $relation;
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return parent::__callStatic($method, $parameters);
    }
}
