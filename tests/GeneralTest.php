<?php

namespace Tkeer\Flattable\Test;

use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\BookFlattable;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\PublisherFlattable;

class GeneralTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_not_create_new_entry_flattable_when_main_table_entry_is_created_when_flattable_is_disabled()
    {
        Book::disableFlattable();
        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->first();

        $this->assertNull($bookFlattable);
        Book::enableFlattable();
    }

    /**
     * @test
     */
    public function it_will_create_new_entry_flattable_when_main_table_entry_is_created_when_flattable_is_enabled_after_disable()
    {
        Book::disableFlattable();
        $this->assertTrue(Book::$flattableDisabled);
        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->first();
        $this->assertNull($bookFlattable);

        Book::enableFlattable();
        $this->assertFalse(Book::$flattableDisabled);

        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->first();
        $this->assertNotNull($bookFlattable);
    }

    /**
     * @test
     */
    public function it_works_for_accessors()
    {
        $publisher = factory(Publisher::class)->create();
        $flattable = PublisherFlattable::where('publisher_id', $publisher->id)->first();

        $this->assertNotNull($publisher->name);
        $this->assertEquals($publisher->name, $flattable->name);
    }

    /**
     * @test
     */
    public function it_will_update_flattable_if_only_accessor_is_updated()
    {
        $publisher = new class extends Publisher {
            protected $table = 'publishers';
            public function flattableConfig(): array
            {
                return [
                    [
                        'columns' => [
                            'name' => 'name',
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
                ];
            }
        };
        $publisher->fill(['first_name' => 'firstName', 'last_name' => 'lastName'])->save();
        $flattable = PublisherFlattable::where('publisher_id', $publisher->id)->first();

        $this->assertNotNull($flattable);
        $this->assertNotNull($flattable->name);
        $this->assertNull($flattable->first_name);
        $this->assertEquals($flattable->name, 'firstName lastName');

        $publisher->update(['first_name' => 'first_name']);
        $flattable->refresh();
        $this->assertEquals($flattable->name, 'first_name lastName');

    }

    /**
     * @todo how to test it?
     */
    public function it_wont_make_any_database_query_is_non_of_column_in_config_is_updated()
    {
        $publisher = factory(Publisher::class)->create();
        $flattable = PublisherFlattable::where('publisher_id', $publisher->id)->first();

        $queryExecuted = false;

        $publisher->update(['established_at' => now()]);
        $this->assertFalse($queryExecuted);
    }

    /**
     * @test
     */
    public function it_wont_run_when_flattable_is_disabled_in_config()
    {
        config()->set('flattable.disabled', true);

        $publisher = factory(Publisher::class)->create();
        $flattable = PublisherFlattable::where('publisher_id', $publisher->id)->first();

        $this->assertNull($flattable);
    }

    /**
     * @test
     */
    public function it_wont_run_when_flattable_is_console_run_disabled_in_config()
    {
        config()->set('flattable.console.run', false);

        $publisher = factory(Publisher::class)->create();
        $flattable = PublisherFlattable::where('publisher_id', $publisher->id)->first();

        $this->assertNull($flattable);

    }
}
