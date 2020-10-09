<?php

namespace Tkeer\Flattable\Test;

use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\Country;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\ReadingActivity;
use Tkeer\Flattable\Test\Models\ReadingActivityFlattable;

class FillFlattableCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_fills_flattable()
    {
        config()->set('flattable.disabled', true);
        $this->createEntriesInRelatedTables();
        $this->createEntriesInRelatedTables();
        $this->assertEmpty(ReadingActivityFlattable::count());

        $this->artisan('flattable:fill', ['model' => ReadingActivity::class]);

        $this->assertEquals(2, ReadingActivityFlattable::count());

        $activity = ReadingActivity::first();
        $flattable = ReadingActivityFlattable::where('activity_id', $activity->id)->first();

        $this->assertNotNull($flattable);
        $this->assertEquals($activity->book_id, $flattable->book_id);
        $this->assertEquals($activity->book->publisher_id, $flattable->publisher_id);
        $this->assertEquals($activity->book->publisher->country_id, $flattable->publisher_country_id);
    }

    private function createEntriesInRelatedTables()
    {
        $country = Country::create(['name' => $this->faker->country, 'id' => $this->faker->unique()->randomDigitNotNull]);
        $publisher = factory(Publisher::class)->create(['country_id' => $country->id]);
        $book = factory(Book::class)->create(['publisher_id' => $publisher->id]);
        $activity = ReadingActivity::create(['book_id' => $book->id]);

        return compact('country', 'publisher', 'book', 'activity');
    }
}