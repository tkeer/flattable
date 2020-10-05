<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;
use Tkeer\Flattable\Test\Database\Factories\AuthorFactory;
use Tkeer\Flattable\Test\Database\Factories\BookFactory;

class SoftDeletedAuthor extends Author
{
    protected $table = 'authors';
    protected $guarded = [];

    use SoftDeletes;
}
