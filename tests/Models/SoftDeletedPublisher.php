<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tkeer\Flattable\Flattable;
use Tkeer\Flattable\Test\Database\Factories\PublisherFactory;

class SoftDeletedPublisher extends Publisher
{
    protected $table = 'publishers';
    protected $guarded = [];

    use SoftDeletes;
}
