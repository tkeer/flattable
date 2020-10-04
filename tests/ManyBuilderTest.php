<?php

namespace Tkeer\Flattable\Test;

use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\PublisherFlattable;

class ManyBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_many_entries_where_primary_row_is_created()
    {
        $publisher = Publisher::factory()->create();

        $book = Book::factory()->create(['publisher_id' => $publisher->id]);
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();

        $flattableBooks = json_decode($bookFlattable->books, true);

        $this->assertEquals($flattableBooks[0]['id'], $book->id);
        $this->assertEquals($flattableBooks[0]['name'], $book->name);

        $book = Book::factory()->create(['publisher_id' => $publisher->id]);
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();

        $flattableBooks = json_decode($bookFlattable->books, true);

        $this->assertEquals($flattableBooks[1]['id'], $book->id);
        $this->assertEquals($flattableBooks[1]['name'], $book->name);
    }

    /**
     * @test
     */
    public function it_updates_json_entries_in_flattable_when_source_table_is_updated()
    {
        $publisher = Publisher::factory()->create();

        $book = Book::factory()->create(['publisher_id' => $publisher->id]);

        $publisherFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($publisherFlattable->books, true);
        $this->assertEquals($flattableBooks[0]['name'], $book->name);

        $book->update(['name' => $this->faker->name]);

        $publisherFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
//        dd($publisherFlattable->books);
        $flattableBooks = json_decode($publisherFlattable->books, true);
        $this->assertEquals($flattableBooks[0]['name'], $book->name);
    }

    /**
     * @test
     */
    public function it_removes_entries_from_json_in_flattable_where_source_table_entry_is_deleted()
    {
        $publisher = Publisher::factory()->create();

        Book::factory()->create(['publisher_id' => $publisher->id]);
        Book::factory()->create(['publisher_id' => $publisher->id]);
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(2, $flattableBooks);

        Book::where('publisher_id', $publisher->id)->first()->delete();
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(1, $flattableBooks);

        Book::where('publisher_id', $publisher->id)->first()->delete();
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(0, $flattableBooks);
    }
}