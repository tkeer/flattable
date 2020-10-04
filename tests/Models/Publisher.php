<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Flattable;
use Tkeer\Flattable\Test\Database\Factories\PublisherFactory;

class Publisher extends Model
{
    use Flattable/*, HasFactory*/;

    protected $guarded = [];

    public function flattableConfig()
    {
        return [
//            // updates entries in books flattable
            [
                'columns' => [
                    'publisher_first_name' => 'first_name',
                    'publisher_last_name' => 'last_name',
                ],
                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'publisher_id',
                    ],
                ],
                'type' => 'secondary',

                'flat_table' => 'books_flattable',
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
                        'col_name' => 'id',
                        'flat_table_col_name' => 'publisher_id',
                    ],
                ],

                'type' => 'primary',

                'flat_table' => 'publishers_flattable',
            ],

            //update entries in reading activities flattable

            [
                'columns' => [
                    'publisher_id' => 'id',
                    'publisher_first_name' => 'first_name'
                ],

                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'publisher_id',
                    ]
                ],

                'flat_table' => 'reading_activities_flattable',
                'type' => 'secondary',

                'changes' => [
                    'country_id' => [

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
                        'table' => 'countries'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory()
    {
        return PublisherFactory::new();
    }

}
