<?php

namespace Tkeer\Flattable;

trait Flattable
{
    public static function bootFlattable()
    {
        static::observe(FlattableModelObserver::class);
    }

    public abstract function flattableConfig(): array;
}