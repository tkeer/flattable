<?php

namespace Tkeer\Flattable\Test;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Tkeer\Flattable\DatabaseManager;
use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\BookFlattable;
use Tkeer\Flattable\Test\Models\Country;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\PublisherFlattable;
use Tkeer\Flattable\Test\Models\ReadingActivity;
use Tkeer\Flattable\Test\Models\ReadingActivityFlattable;
use Tkeer\Flattable\Test\Models\SoftDeletedAuthor;
use Tkeer\Flattable\Test\Models\SoftDeletedBook;
use Tkeer\Flattable\Test\Models\SoftDeletedCountry;
use Tkeer\Flattable\Test\Models\SoftDeletedPublisher;
use Tkeer\Flattable\Test\Models\SoftDeletedReadingActivity;

class SoftDeletedModelTest extends TestCase
{
    /**
     * @test
     */
    public function it_deletes_flattable_entry_when_main_table_entry_is_deleted()
    {
        $book = SoftDeletedBook::create(factory(Book::class)->make()->toArray());
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->name, $book->name);

        $book->delete();

        $this->assertNull(BookFlattable::where('book_id', $book->id)->first());
    }

    /**
     * @test
     */
    public function it_remove_changes_entries_in_flattable_when_secondary_table_entry_is_deleted()
    {
        $publisher = factory(SoftDeletedPublisher::class)->create();
        $book = factory(SoftDeletedBook::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = BookFlattable::where('book_id', $book->id)->firstOrFail();

        $this->assertEquals($bookFlattable->publisher_first_name, $publisher->first_name);

        $publisher->delete();

        $bookFlattable->refresh();

        $this->assertNull($bookFlattable->publisher_first_name);
    }

    /**
     * @test
     */
    public function it_removes_entries_from_json_in_flattable_where_source_table_entry_is_deleted()
    {
        $publisher = factory(Publisher::class)->create();

        factory(SoftDeletedBook::class)->create(['publisher_id' => $publisher->id]);
        factory(SoftDeletedBook::class)->create(['publisher_id' => $publisher->id]);
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(2, $flattableBooks);

        SoftDeletedBook::where('publisher_id', $publisher->id)->first()->delete();
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(1, $flattableBooks);

        SoftDeletedBook::where('publisher_id', $publisher->id)->first()->delete();
        $bookFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();
        $flattableBooks = json_decode($bookFlattable->books, true);
        $this->assertCount(0, $flattableBooks);
    }

    /**
     * @test
     */
    public function it_nullify_country_in_flattable_where_publisher_country_is_deleted()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($activityFlattable->publisher_country_name);

        $country->delete();
        $activityFlattable->refresh();
        $this->assertNull($activityFlattable->publisher_country_name);
    }

    /**
     * @test
     *
     * 2nd level changes
     */
    public function it_nullify_publisher_in_flattable_where_publisher_is_deleted()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($activityFlattable->publisher_id);

        $publisher->delete();
        $activityFlattable->refresh();
        $this->assertNull($activityFlattable->publisher_id);
    }

    /**
     * @test
     *
     * 3rd level changes
     */
    public function it_nullify_country_in_flattable_where_country_is_deleted()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($activityFlattable->publisher_country_id);

        $country->delete();
        $activityFlattable->refresh();
        $this->assertNull($activityFlattable->publisher_country_id);
    }

    /**
     * @test
     *
     * have to delete country explicitly
     */
    public function it_wont_nullify_country_in_flattable_where_country_is_deleted()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($activityFlattable->publisher_country_id);

        $publisher->delete();
        $activityFlattable->refresh();
        $this->assertNotNull($activityFlattable->publisher_country_id);
    }


    private function createEntriesInRelatedTables()
    {
        $country = SoftDeletedCountry::create(['name' => $this->faker->country]);
        $publisher = factory(SoftDeletedPublisher::class)->create(['country_id' => $country->id]);
        $book = factory(SoftDeletedBook::class)->create(['publisher_id' => $publisher->id]);
        $activity = SoftDeletedReadingActivity::create(['book_id' => $book->id]);

        return compact('country', 'publisher', 'book', 'activity');
    }


}