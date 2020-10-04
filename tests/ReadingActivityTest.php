<?php

namespace Tkeer\Flattable\Test;

use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\BookFlattable;
use Tkeer\Flattable\Test\Models\Country;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\ReadingActivity;
use Tkeer\Flattable\Test\Models\ReadingActivityFlattable;

class ReadingActivityTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_flattable_entry_when_reading_activity_is_created()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($activityFlattable);
    }

    /**
     * @test
     */
    public function it_updates_country_name_in_flattable_when_country_name_in_countries_table_is_updated()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($country->name, $activityFlattable->publisher_country_name);

        $country->update(['name' => $this->faker->country]);

        $activityFlattable->refresh();
        $this->assertEquals($country->name, $activityFlattable->publisher_country_name);
    }

    /**
     * @test
     */
    public function it_updates_country_in_flattable_when_books_publisher_is_changes()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($country->name, $activityFlattable->publisher_country_name);

        $newCountry = Country::create(['name' => $this->faker->country]);
        $publisher->update(['country_id' => $newCountry->id]);

        $activityFlattable->refresh();
        $this->assertEquals($newCountry->id, $activityFlattable->publisher_country_id);
        $this->assertEquals($newCountry->name, $activityFlattable->publisher_country_name);
    }

    /**
     * @test
     */
    public function it_updates_publisher_in_flattable_when_books_publisher_is_updated()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($publisher->first_name, $activityFlattable->publisher_first_name);

        $publisher->update(['first_name' => $this->faker->firstName]);

        $activityFlattable->refresh();

        $this->assertEquals($publisher->first_name, $activityFlattable->publisher_first_name);

    }

    /**
     * @test
     */
    public function it_updates_book_in_flattable_when_books_is_updated()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $book = $models['book'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($book->name, $activityFlattable->book_name);

        $book->update(['name' => $this->faker->name]);

        $activityFlattable->refresh();
        $this->assertEquals($book->name, $activityFlattable->book_name);
    }

    /**
     * @test
     */
    public function it_updates_publisher_in_flattable_when_books_is_changed()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $book = $models['book'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($publisher->first_name, $activityFlattable->publisher_first_name);
        $this->assertEquals($publisher->id, $activityFlattable->publisher_id);

        $newPublisher = factory(Publisher::class)->create();
        $newBook = $book->update(['publisher_id' => $newPublisher->id]);

        $activityFlattable->refresh();

        $this->assertEquals($newPublisher->first_name, $activityFlattable->publisher_first_name);

    }
    /**
     * @test
     */
    public function it_updates_publisher_country_in_flattable_when_books_is_changed()
    {
        $models = $this->createEntriesInRelatedTables();
        $activity = $models['activity'];
        $country = $models['country'];
        $book = $models['book'];
        $publisher = $models['publisher'];
        $activityFlattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertEquals($publisher->first_name, $activityFlattable->publisher_first_name);
        $this->assertEquals($publisher->id, $activityFlattable->publisher_id);

        $newCountry = Country::create(['name' => $this->faker->country]);
        $newPublisher = factory(Publisher::class)->create(['country_id' => $newCountry->id]);
        $book->update(['publisher_id' => $newPublisher->id]);


        $activityFlattable->refresh();

        $this->assertEquals($newCountry->name, $activityFlattable->publisher_country_name);
        $this->assertEquals($newCountry->id, $activityFlattable->publisher_country_id);

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
        $publisher = $models['publisher'];
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
        $country = Country::create(['name' => $this->faker->country]);
        $publisher = factory(Publisher::class)->create(['country_id' => $country->id]);
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $activity = ReadingActivity::create(['book_id' => $book->id]);

        return compact('country', 'publisher', 'book', 'activity');
    }
}