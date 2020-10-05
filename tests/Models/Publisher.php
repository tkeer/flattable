<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Flattable;

class Publisher extends Model
{
    use Flattable;

    protected $guarded = [];

    public function flattableConfig()
    {
        return [
            // updates entries in books flattable
            [
                'columns' => [
                    'publisher_first_name' => 'first_name',
                    'publisher_last_name' => 'last_name',
                ],
                'wheres' => [
                    [
                        'column_name' => 'id',
                        'flattable_column_name' => 'publisher_id',
                    ],
                ],
                'type' => 'secondary',

                'flattable' => 'books_flattable',
            ],

            //updates_entries_own_publishers_flattable
            [
                'columns' => [
                    'first_name' => 'first_name',
                    'last_name' => 'last_name',
                    'publisher_id' => 'id',
                ],
                'wheres' => [
                    [
                        'column_name' => 'id',
                        'flattable_column_name' => 'publisher_id',
                    ],
                ],

                'type' => 'primary',

                'flattable' => 'publishers_flattable',
            ],

            //update entries in reading activities flattable

            [
                'columns' => [
                    'publisher_id' => 'id',
                    'publisher_first_name' => 'first_name'
                ],

                'wheres' => [
                    [
                        'column_name' => 'id',
                        'flattable_column_name' => 'publisher_id',
                    ]
                ],

                'flattable' => 'reading_activities_flattable',
                'type' => 'secondary',

                'changes' => [
                    'country_id' => [

                        'columns' => [
                            'publisher_country_name' => 'name',
                            'publisher_country_id' => 'id'
                        ],
                        'wheres' => [
                            [
                                'column_name' => 'id',
                                'flattable_column_name' => 'publisher_country_id',
                            ]
                        ],
                        'type' => 'secondary',
                        'table' => 'countries'
                    ]
                ]
            ]
        ];
    }
}
