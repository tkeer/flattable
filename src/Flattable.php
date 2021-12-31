<?php

namespace Tkeer\Flattable;

trait Flattable
{
    /**
     * @var bool
     */
    public static $flattableDisabled = false;

    public static function bootFlattable()
    {
        static::observe(FlattableModelObserver::class);
    }

    public static function isFlattableDisabled()
    {
        return static::$flattableDisabled == true;
    }

    /**
     * Disable Auditing.
     *
     * @return void
     */
    public static function disableFlattable()
    {
        static::$flattableDisabled = true;
    }

    /**
     * Enable Auditing.
     *
     * @return void
     */
    public static function enableFlattable()
    {
        static::$flattableDisabled = false;
    }

    public abstract function flattableConfig(): array;
}
