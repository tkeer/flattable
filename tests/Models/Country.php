<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;
use function Tkeer\Flattable\Builders\is_deleted;

class Country extends Model
{
    use Flattable;

    protected $guarded = [];

    public $timestamps = false;

    public function flattableConfig(): array
    {
        return [
            [
                'columns' => function (Country $country) {
                    // when secondary row is deleted, it's data should be removed from flattable
                    $country = is_deleted($country) ? new Country : $country;
                    return [
                        'publisher_country_name' => $country->name,
                        'publisher_country_id' => $country->id
                    ];
                },
                'wheres' => function (Builder $db, Country $model) {
                    $db->where('publisher_country_id', $model->id);
                },
                'type' => 'secondary',
                'flattable' => 'reading_activities_flattable',
            ]
        ];
    }
}
