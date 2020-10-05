<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tkeer\Flattable\Flattable;

class SoftDeletedBook extends Book
{
    protected $table = 'books';

    use SoftDeletes;

}
