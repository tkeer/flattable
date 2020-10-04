<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;
use Tkeer\Flattable\Test\Database\Factories\AuthorFactory;
use Tkeer\Flattable\Test\Database\Factories\BookFactory;

class Author extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AuthorFactory::new();
    }
}
