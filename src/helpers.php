<?php

namespace Tkeer\Flattable\Builders
{
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
}