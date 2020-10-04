<?php

namespace Tkeer\Flattable\Test\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tkeer\Flattable\Flattable;

class ReadingActivity extends Model
{
    use Flattable;

    protected $guarded = [];

    public $timestamps = false;

    public function flattableConfig(): array
    {
        return [
            [
                'columns' => [
                    'activity_id' => 'id'
                ],
                'wheres' => [
                    [
                        'col_name' => 'id',
                        'flat_table_col_name' => 'activity_id',
                    ],
                ],

                'flat_table' => 'reading_activities_flattable',

                'type' => 'primary',

                'changes' => [
                    'book_id' => [
                        'columns' => [
                            'book_id' => 'id',
                            'book_name' => 'name',
                            'publisher_id' => 'publisher_id'
                        ],
                        'table' => 'books',

                        'changes' => [
                            'publisher_id' => [
                                'columns' => [
                                    'publisher_first_name' => 'first_name',
                                    'publisher_last_name' => 'last_name',
                                    'publisher_id' => 'id'
                                ],
                                'table' => 'publishers',

                                'changes' => [
                                    'country_id' => [
                                        'columns' => [
                                            'publisher_country_id' => 'id',
                                            'publisher_country_name' => 'name'
                                        ],
                                        'table' => 'countries'
                                    ]
                                ]

                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
