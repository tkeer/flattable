<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;

class Country extends Model
{
    use Flattable;

    protected $guarded = [];

    public $timestamps = false;

    public function flattableConfig(): array
    {
        return [
            [
                'columns' => [
                    'publisher_country_name' => 'name',
                    'publisher_country_id' => 'id'
                ],
                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'publisher_country_id',
                    ]
                ],
                'type' => 'secondary',
                'flat_table' => 'reading_activities_flattable',
            ]
        ];
    }
}
