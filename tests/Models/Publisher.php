<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Flattable;

class Publisher extends Model
{
    use Flattable;

    protected $guarded = [];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function flattableConfig()
    {
        return [

            //updates_entries_own_publishers_flattable
            [
                'columns' => [
                    'first_name' => 'first_name',
                    'last_name' => 'last_name',
                    'name' => 'name',
                    'publisher_id' => 'id',
                ],
                'wheres' => [
                    'publisher_id' => 'id',
                ],

                'type' => 'primary',

                'flattable' => 'publishers_flattable',
            ],
            // updates entries in books flattable
            [
                'columns' => [
                    'publisher_first_name' => 'first_name',
                    'publisher_last_name' => 'last_name',
                ],
                'wheres' => [
                    'publisher_id' => 'id',
                ],
                'type' => 'secondary',

                'flattable' => 'books_flattable',
            ],


            //update entries in reading activities flattable

            [
                'columns' => [
                    'publisher_id' => 'id',
                    'publisher_first_name' => 'first_name'
                ],

                'wheres' => [
                    'publisher_id' => 'id',
                ],

                'flattable' => 'reading_activities_flattable',
                'type' => 'secondary',

                'changes' => [
                    'country_id' => [

                        'columns' => [
                            'publisher_country_name' => 'name',
                            'publisher_country_id' => 'id'
                        ],
                        'where' => [
                            'id' => 'country_id'
                        ],
                        'table' => 'countries'
                    ]
                ]
            ]
        ];
    }
}
