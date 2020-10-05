<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Flattable;

class Book extends Model
{
    use Flattable;

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
                        'column_name' => 'id',
                        'flattable_column_name' => 'book_id',
                    ],
                ],

                'flattable' => 'books_flattable',

                'changes' => [
                    'publisher_id' => [
                        'columns' => [
                            'publisher_first_name' => 'first_name',
                            'publisher_last_name' => 'last_name',

                            /**
                             * make sure to add connecting columns for nested 'changes', otherwise proper data would not be fetched
                             *
                             * in this particular example, if country id is not fetched, then country_id in nested 'changes'
                             * config wouldn't be fetched
                             */
                            'publisher_country_id' => 'country_id'
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
                                        'column_name' => 'id',
                                        'flattable_column_name' => 'publisher_country_id',
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
                        'column_name' => 'publisher_id',
                        'flattable_column_name' => 'publisher_id',
                    ],
                ],
                //if publisher id changes, have to remove this tank from old publisher
                //only delete from old if these keys have changed
                'delete_from_old_keys' => ['publisher_id'],

                'flattable' => 'publishers_flattable',

                'flattable_column_name' => 'books',
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
                        'column_name' => 'publisher_id',
                        'flattable_column_name' => 'publisher_id',
                    ],
                ],
                //if publisher id changes, have to remove this tank from old publisher
                //only delete from old if these keys have changed
                'delete_from_old_keys' => ['publisher_id'],

                'flattable' => 'publishers_flattable',

                'flattable_column_name' => 'recent_books',
            ],
            [
                'columns' => [
                    'book_id' => 'id',
                    'book_name' => 'name'
                ],
                'wheres' => [
                    [
                        'column_name' => 'id',
                        'flattable_column_name' => 'book_id',
                    ]
                ],

                'type' => 'secondary',
                'flattable' => 'reading_activities_flattable',

                'changes' => [
                    'publisher_id' => [
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
                                        'column_name' => 'id',
                                        'flattable_column_name' => 'publisher_country_id',
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
}
