<?php

namespace Tkeer\Flattable\Test;

use Tkeer\Flattable\Test\Models\Book;
use Tkeer\Flattable\Test\Models\Publisher;
use Tkeer\Flattable\Test\Models\PublisherFlattable;

class GeneralTest extends TestCase
{
    /**
     * @t2es2t2
     *
     * sometimes we need to updated flattable tables on on certain conditions
     * ie if flattable contains column of
     */
    public function it_dont_update_flattable_it_modal_constraints_fails()
    {
        $publisher = Publisher::factory()->create();

        $book = Book::factory(['publisher_id' => $publisher->id, 'published_at' => '2020'])->create();

        $publisherFlattable = PublisherFlattable::where('publisher_id', $publisher->id)->firstOrFail();

        $this->assertNotNull($publisherFlattable->recent_books);
//        $this->assertNull($publisherFlattable->recent_books);

        $book->update(['published_at' => '2010']);

        $publisherFlattable->refresh();

        $this->assertNull($publisherFlattable->recent_books);
    }
}