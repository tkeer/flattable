<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Flattable;
use Tkeer\Flattable\Test\Database\Factories\BookFactory;

class Book extends Model
{
    use Flattable, HasFactory;

    protected $guarded = [];

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function flattableConfig(): array
    {
        return [
            [
                'columns' => [
                    'name' => 'name',
                    'published_at' => 'published_at',
                    'publisher_id' => 'publisher_id',
                    'book_id' => 'id'
                ],
                'type' => 'primary',
                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'book_id',
                    ],
                ],

                'flat_table' => 'books_flattable',

                'changes' => [
                    'publisher_id' => [
                        'columns' => [
                            'publisher_first_name' => 'first_name',
                            'publisher_last_name' => 'last_name',

                            /**
                             * @todo agr yaha country id na add kro, books k lye country ka data nahi ata,
                             * q k publishers s country id select nahi hui
                             * iska hal ye ho skta h k jo columns change mei h unka data automatically load kr liya jye
                             */
                            'publisher_country_id' => 'country_id'
//                            'publisher_country' => 'country',
                        ],

                        'column_name' => 'id', //column name to use for fetching data from source table

                        'model_column_name' => 'publisher_id',

                        'type' => 'secondary',

                        'table' => 'publishers',

                        'changes' => [
                            'country_id' => [
                                'columns' => [
                                    'publisher_country_name' => 'name',
                                    'publisher_country_id' => 'id',
                                ],
                                'wheres' => [
                                    [
                                        'col_name' => 'id',
                                        'flat_table_col_name' => 'publisher_country_id',
                                    ],
                                ],
                                'type' => 'secondary',
                                'model_column_name' => 'country_id',
                                'table' => 'countries'
                            ]
                        ]

                    ]
                ]
            ],
            [
                'columns' => [
                    'id' => 'id',
                    'name' => 'name'
                ],

                'type' => 'many',

                'wheres' => [
                    [
                        'col_name' => 'publisher_id',
                        'flat_table_col_name' => 'publisher_id',
                    ],
                ],
                //if publisher id changes, have to remove this tank from old publisher
                //only delete from old if these keys have changed
                'delete_from_old_keys' => ['publisher_id'],

                'flat_table' => 'publishers_flattable',

                'flat_table_json_col_name' => 'books',
            ],


            [
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'published_at' => 'published_at'
                ],

                'type' => 'many',

                'wheres' => [
                    [
                        'col_name' => 'publisher_id',
                        'flat_table_col_name' => 'publisher_id',
                    ],
                ],

                //only update flat table if active model pass these constraints
                'model2_constraints' => [
                    [
                        'attribute' => 'published_at',
                        'op' => '>=',
                        'value' => '2020',
                    ],

                ],
                //if publisher id changes, have to remove this tank from old publisher
                //only delete from old if these keys have changed
                'delete_from_old_keys' => ['publisher_id'],

                'flat_table' => 'publishers_flattable',

                'flat_table_json_col_name' => 'recent_books',
            ],













            [
                'columns' => [
                    'book_id' => 'id',
                    'book_name' => 'name'
                ],
                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'book_id',
                    ]
                ],

                'type' => 'secondary',
                'flat_table' => 'reading_activities_flattable',

                'changes' => [
                    'publisher_id' => [
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
                        'type' => 'secondary',
                        'table' => 'publishers',

                        'changes' => [
                            'country_id' => [
                                'columns' => [
                                    'publisher_country_id' => 'id',
                                    'publisher_country_name' => 'name'
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
                ]
            ]
        ];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return BookFactory::new();
    }
}
