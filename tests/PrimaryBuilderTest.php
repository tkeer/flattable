<?php

namespace Tkeer\Flattable\Test;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Tkeer\Flattable\DatabaseManager;
use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\BookFlattable;
use Tkeer\Flattable\Test\Models\Country;
use Tkeer\Flattable\Test\Models\Publisher;

class PrimaryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_new_entry_flattable_when_main_table_entry_is_created()
    {
        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->book_id, $book->id);
    }

    /**
     * @test
     */
    public function it_updates_flattable_entry_when_main_table_entry_is_updated()
    {
        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->name, $book->name);

        $book->update(['name' => $this->faker->name]);

        $bookFlattable->refresh();

        $this->assertEquals($bookFlattable->name, $book->name);
    }

    /**
     * @test
     */
    public function it_deletes_flattable_entry_when_main_table_entry_is_deleted()
    {
        $book = factory(Book::class)->create();
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->name, $book->name);

        $book->delete();

        $this->assertNull(BookFlattable::where('book_id', $book->id)->first());
    }

    /**
     * @test
     */
    public function it_add_changes_entries_in_flattable_when_secondary_table_entry_is_updated()
    {
        $publisher = factory(Publisher::class)->create();
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->publisher_first_name, $publisher->first_name);

        $publisher->update(['first_name' => $this->faker->firstName]);

        $bookFlattable->refresh();

        $this->assertEquals($bookFlattable->publisher_first_name, $publisher->first_name);
    }

    /**
     * @test
     */
    public function it_remove_changes_entries_in_flattable_when_secondary_table_entry_is_deleted()
    {
        $publisher = factory(Publisher::class)->create();
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->publisher_first_name, $publisher->first_name);

        $publisher->delete();

        $bookFlattable->refresh();

        $this->assertNull($bookFlattable->publisher_first_name);
    }

    /**
     * @test
     */
    public function it_adds_changes_entries_of_secondary_when_secondary_changes_entries_added()
    {
        $country = Country::create(['name' => $this->faker->country]);
        $publisher = factory(Publisher::class)->create(['country_id' => $country->id]);
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->publisher_country_name, $country->name);
    }

    /**
     * @test
     */
    public function it_lets_use_use_callback_to_make_data_to_be_saved_in_flattable()
    {
        $country = Country::create(['name' => $this->faker->country]);

        $publisher = factory(Publisher::class)->create(['country_id' => $country->id]);
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->publisher_country_name, $country->name);
    }
}