<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Author;
use PHPUnit\Framework\Attributes\Test; 

class BookUnitTest extends TestCase
{
    use RefreshDatabase;

    #[Test] // Use this attribute instead of /** @test */
    public function it_can_get_all_books()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);

        Book::factory()->count(2)->create([
            'author_id' => $author->id,
        ]);

        $response = $this->getJson('/api/books');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ])
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_can_get_a_single_book()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);

        $book = Book::factory()->create([
            'author_id' => $author->id,
        ]);

        $response = $this->getJson("/api/books/{$book->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'price' => $book->price,
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_book()
    {
        $response = $this->getJson('/api/books/9999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Book not found'
                ]);
    }

    #[Test]
    public function it_can_create_a_book()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);

        $data = [
            'title' => 'New Book',
            'description' => 'Some description',
            'price' => 99.99,
            'author_id' => $author->id,
        ];

        $response = $this->postJson('/api/books', $data);

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Book created successfully'
            ]);

        $this->assertDatabaseHas('books', [
            'title' => 'New Book'
        ]);
    }

    #[Test]
    public function it_fails_to_create_book_with_invalid_data()
    {
        $data = [
            'title' => '',
            'description' => 'Invalid book',
            'price' => 'abc',
            'author_id' => 9999,
        ];

        $response = $this->postJson('/api/books', $data);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_update_a_book()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);
        
        $book = Book::factory()->create([
            'author_id' => $author->id,
        ]);

        $data = [
            'title' => 'Updated Book',
            'description' => 'Updated description',
            'price' => 120,
            'author_id' => $author->id,
        ];

        $response = $this->putJson("/api/books/{$book->id}", $data);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book updated successfully',
                'data' => [
                    'id' => $book->id,
                    'title' => 'Updated Book',
                ]
            ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Updated Book'
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_nonexistent_book()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);

        $data = [
            'title' => 'Does not exist',
            'description' => 'Nothing',
            'price' => 50,
            'author_id' => $author->id,
        ];

        $response = $this->putJson('/api/books/9999', $data);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Book not found'
                ]);
    }

    #[Test]
    public function it_can_delete_a_book()
    {
        $author = Author::create([
            'name' => 'Test Author'
        ]);
        
        $book = Book::factory()->create([
            'author_id' => $author->id,
        ]);

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book deleted successfully'
            ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id
        ]);
    }

    #[Test]
    public function it_returns_404_when_deleting_nonexistent_book()
    {
        $response = $this->deleteJson('/api/books/9999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Book not found'
                ]);
    }
}