<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;

class ReadingActivityFlattable extends Model
{
    protected $table = 'reading_activities_flattable';
}
