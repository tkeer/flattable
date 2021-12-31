<p align="center">

<img src="https://user-images.githubusercontent.com/20635376/95227830-06b9a880-0818-11eb-9681-5ee6dd569401.png" alt="laravel flattable">
</p>


Laravel Flattable [![Build Status](https://travis-ci.org/tkeer/flattable.svg?branch=master)](https://travis-ci.org/tkeer/flattable) [![Build Status](https://img.shields.io/packagist/v/tkeer/flattable.svg??style=flat-square)](https://packagist.org/packages/tkeer/flattable) [![Build Status](https://img.shields.io/packagist/l/tkeer/flattable.svg?style=flat-square)](https://packagist.org/packages/tkeer/flattable)
===============


It lets you manage de-normalized tables with simple 
configurations.

Just add array based config in your models and it will
automatically sync your denormalized tables. 


Introduction
----------

Do you currenlty have de-normalized tables in your laravel application, or planning to have one, this package can help you keep your denormalized tables synced with source tables.

With flattable, you can combine multiple tables into one big table and improve performance by:

Minimizing the need for joins and subqueries, precomputing aggregate values, that is, computing them at data modification time, rather than at select time

<p>

<img height="300px" src="https://user-images.githubusercontent.com/20635376/95441979-3da4d100-0974-11eb-9855-1f902e1eb3df.png" alt="laravel flattable">
</p>


You just have to create a flat table, add configuration in related models,
and everything will start working automatically.


Installation
------------
Install the package via Composer:

```bash
composer require tkeer/flattable
```

### Laravel version Compatibility
 Laravel  | Package
:---------|:----------
 8.x\|php8      | 3.x
 8.x\|php7      | 2.x
 <8.x          | 1.x

Usage
------------
1. Add `Flattable` trait into your model
2. Implement `flattableConfig` method and add your configurations

Learn with examples
-------

It is easier to explain it with the help of examples. For more detailed examples, please review the [tests](https://github.com/tkeer/flattable/tree/master/tests)


***Example DB structure***

1. We have books, publishers, countries tables
2. A book belongs to a publisher
3. A publisher belongs to a country


We want data of the book, book's publisher and country of the book's publisher in book's flattable (books_flattable)

<img height="300px" src="https://user-images.githubusercontent.com/20635376/95441979-3da4d100-0974-11eb-9855-1f902e1eb3df.png" alt="laravel flattable">

As book is main table here, we will add flattable configuration in the book's model, and the `type` should be `primary`, more on type [here](#5-type).

To explain the problem, we will break our configurations into 3 parts. For detailed configuration for the book, please see first config entry of [book](https://github.com/tkeer/flattable/blob/master/tests/Models/Book.php#L22)'s model in tests.

### 1. Book in book's flattable
> also updates/deletes when related book is updated or deleted

in `getFlattableConfig()` method of the `Book` model

```php
public function getFlattableConfig(): array
{
  [
    [

      'columns' => [

        //flattable column => 'source model column'
        'name' => 'name',
        'published_at' => 'published_at',
        'publisher_id' => 'publisher_id',
        'book_id' => 'id'

      ],

      // type of relationship b/w flattable and model
      'type' => 'primary',

      // how to find related entry in the flattable table
      'wheres' => [

        // key is flattable column
        // value is column of source table (book)
        'book_id' => 'id',

      ],

      'flattable' => 'books_flattable',
    ]
  ]
}
```

### 2. Publisher in the book's flattable.
> it also updates flattable with new publisher when book's publisher is changed

Extend flattable config used above, and add config for publisher under `changes` key.

```php
public function getFlattableConfig(): array
{
  [

    [

      'flattable' => 'books_flattable',
      ...

      'changes' => [

        // foreign colum name
        // we will update changes data only if this column is update(dirty)

        'publisher_id' => [

          'columns' => [

            'publisher_first_name' => 'first_name',
            'publisher_last_name' => 'last_name',

          ],   

          // talbe name of the source
          'table' => 'publishers',
        ]
      ]
    ]
  ]
}

```

### 3. Country of the publisher in book's flattable

```php
[
    //inside pubilsher config of books flattable
    ...
    'changes' => [
        'country_id' => [
            'columns' => [
                'publisher_country_name' => 'name',
                'publisher_country_id' => 'id',
            ],
            'where' => [
                'id' => 'country_id'
            ],
            'table' => 'countries'
        ]
    ]
]
```

you can go as many nested level as you want using `changes` attribute, ie `changes` attribute within `changes` attribute.


With added configuration so far, any change in the book will automatically update the book's flattable. Even if the publisher of the book is changed, the flattable will automatically be updated with new publisher data.

What if publisher itself is updated, ie first_name of the publisher is updated, or the country of publisher is updated. For this we have to implement flattable for the `Publisher` and `Country` models and add flattable config in both models, and the config `type` should be `secondary`.

See below

### Update book's flattable on publisher update

In `flattableConfig()` of the `Publisher` model

```php
public function flattableConfig()
{
  return [
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
    ]
  ]
}
```

### Update book's flattable on country update
> Assigns null values to flattable when country is deleted

In `flattableConfig()` of the `Country` model

```php
public function flattableConfig()
{
  return [
    [
      'columns' => [
          'publisher_country_name' => 'name',
          'publisher_country_id' => 'id',
      ],
      'wheres' => [
          'publisher_country_id' => 'id',
      ],
      'type' => 'secondary',

      'flattable' => 'books_flattable',
    ]
  ]
}
```


### Books in Publisher's flat table

So far we have considered one-to-one relations, book belongs to one publisher, publisher belongs to one country.

What if there is one to many relationship between two tables.

For example, a publisher can have many books, and whenever any book is added, we want to add this book in the publisher's flattble. 

<img height="300px" src="https://user-images.githubusercontent.com/20635376/95446814-426c8380-097a-11eb-893c-04017763af7c.png">


Add one more flattable config in `Book` model, the config `type` for this relation should be `many`.


```php
public function flattableConfig(): array
{
  ...
  return [
            [
            'columns' => [
                'id' => 'id',
                'name' => 'name'
            ],

            // use type many when you want to store more than one entry in a column
            'type' => 'many',

            'wheres' => [
                'publisher_id' => 'publisher_id',
            ],
            //only delete from old if these keys have changed
            'delete_from_old_keys' => ['publisher_id'],

            'flattable' => 'publishers_flattable',

            // column name of the flaatable, in which the data should be stored.
            'flattable_column_name' => 'books',
        ]
  ]
}

```


Flattable config explanation
-------------

Flattable config has following attributes

[1. columns](#1-columns)

[2. wheres](#2-wheres)

[3. flattable](#3-flattable)

[4. changes](#4-changes)

[5. type](#5-type)

[6. flattable_column_name](#6-flattable_column_name)

[7. delete_from_old_keys](#7-delete_from_old_keys)

[8. deletes_primary](#8-deletes_primary)

#### 1. columns

An array which holds the mapping of flattable columns and source table columns.

Each key in the columns array is the name of the flattable column, and the value is the name of source table column.

```php
[
  'columns' => [
    'book_id' => 'id',
    'book_name' => 'name'
  ]
]
```
#### 2. wheres

An associate array of conditions to map related entry in the flattable.

The key in the sub-array is column name of the flattable and value is column name of the source table.

```php
[
  'wheres' => [
    'book_id' => 'id'
  ]
]
```

#### 3. flattable

Name of the flattable.

#### 4. changes

Include related tables data into the flattable. It should be an associate array.

The key of each array in the changes attribute should be the column name of the source table, whose change loads
the related data in the flattable.

#### 5. type

It describes the relation type b/w flattable and source table

we have three types

##### 1. primary

create, update, and delete do the same operation
for the flattable.

For example, books relation with books_flattable

##### 2. secondary

Same as primary, but deleting model will not delete the related entry in the flattable.
Instead it will assign null values to the related columns in the flattable.

For example, publishers relationship with books_flattable. 
If publisher of the book is deleted, then the publisher's attributes
in the `books_flattable` will be set to `null`.

If you want entry in flattable to be delete for 
`secondary` type, set `deletes_primary` flag to `true`.

##### 3. many

For one to many relationship. With this type, we can store more than one entries
 in the flattable.

For example, books relationship with publishers_flattable, one publisher can have more than one
books.

### 6. flattable_column_name
Required when type is `many`. It holds the column name of the flattable, where json data will be stored.

### 7. delete_from_old_keys
Required when type is `many`. It holds the names of columns, any change in these columns will reload the related json data of related flattable column.

### 8. deletes_primary
primary `type` automaically deletes entry from the flattable, when entry from the main table is deleted, and
secondary type store null values against related entries in flattable.

If you want that deleting an entry for secondary type also deletes related
flattable entry, set `deletes_primary` flag to true.

Configurations
--------------
### Disable flattable a single model
```php
Book::disableFlattable();

$book = factory(Book::class)->create();
$bookFlattable = BookFlattable::where('book_id', $book->id)->first();
$this->assertNull($bookFlattable);

Book::enableFlattable();
```

### Disable flattable for all models

Publish flattable config
```shell script
php artisan vendor:publish --provider="Tkeer\Flattable\FlattableServiceProvider" --tag="config"
```
set `disabled` to `true` in `config/flattabe.php`

```php
return [
    'console' => [
        'run' => true
    ],
    'disabled' => true
];
```

### Disable flattable for console

You can optionally disable flattable when script is 
running through console,

To disable it set `console.run` to `false` in `config/flattable.php`.


### Using callbacks
If none of available options works for your use case, you can pass a callback for `columns` and `wheres` configs.

For `columns` callback, you will receive model as parameter, and you should return data as array to be stored in flattable

```php
[
  ...

  'columns' => function (Country $country) {
    // when secondary row is deleted, it's data should be removed from flattable
    return [
      'publisher_country_name' => $country->exists ? $country->name ? null,
      'publisher_country_id' => $country->exists ? $country->id ? null
    ];
  }

  ...
]
```

For `wheres` callback, you will receive `QueryBuilder` and `Model` as parameters, and you can add as many conditionals as you want. 
```php
[
  ...
  'wheres' => function (Builder $db, Country $model) {
    $db->where('publisher_country_id', $model->id);
  }

  ...
]

```

### Fill flattable

You can use `flattable:fill` command to fill your flattable. 

```shell script
php artisan flattable:fill App\\Models\\Book
```

This command will use primary config of `Book` model
and fill the related flattable
