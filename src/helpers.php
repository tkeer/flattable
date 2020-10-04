<?php

namespace Tkeer\Flattable\Builders
{
    //temp
    //find something else
    function is_deleted($model)
    {
        if (is_null($model)) {
            return true;
        }

        //if soft deleting
        if (method_exists($model, 'trashed')) {
            return $model->trashed();
        }

        return !data_get($model, 'exists');
    }

    function compare($var1, $var2, $op)
    {
        switch ($op) {
            case "=":
                return $var1 == $var2;
            case "!=":
                return $var1 != $var2;
            case ">=":
                return $var1 >= $var2;
            case "<=":
                return $var1 <= $var2;
            case ">":
                return $var1 > $var2;
            case "<":
                return $var1 < $var2;
            default:
                throw new \Exception('Unsupported operator');
        }
    }
}