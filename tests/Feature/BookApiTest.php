<?php

use App\Models\Book;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can get all books', function () {
  $author = Author::factory()->create();

  $books = Book::factory()->count(2)->create([
    'author_id' => $author->id,
  ]);

  $response = $this->getJson('/api/books');

  $response
    ->assertStatus(200)
    ->assertJsonCount(2, 'data')
    ->assertJsonFragment(['id' => $books[0]->id]);
});

it('can get a single book by id', function () {
  $author = Author::factory()->create();
  $book = Book::factory()->create(['author_id' => $author->id]);

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
          'author_id' => $author->id,
      ]
    ]);
});

it('returns 404 when fetching non-existent book', function () {
  $response = $this->getJson('/api/books/9999');
  $response->assertStatus(404);
});

it('can create a book', function () {
  $author = Author::factory()->create();

  $data = [
    'title' => 'Test Book',
    'description' => 'Test description',
    'price' => 100,
    'author_id' => $author->id,
  ];

  $response = $this->postJson('/api/books', $data);

  $response
    ->assertStatus(201)
    ->assertJson([
      'success' => true,
      'message' => 'Book created successfully',
    ]);

  $this->assertDatabaseHas('books', ['title' => 'Test Book']);
});

it('fails to create a book without required fields', function () {
  $response = $this->postJson('/api/books', [
    'title' => 'Incomplete Book',
  ]);

  $response->assertStatus(422);
  $this->assertDatabaseMissing('books', ['title' => 'Incomplete Book']);
});

it('fails to create a book with invalid price', function () {
  $author = Author::factory()->create();

  $data = [
    'title' => 'Invalid Price Book',
    'description' => 'Some desc',
    'price' => 'not-a-number',
    'author_id' => $author->id,
  ];

  $response = $this->postJson('/api/books', $data);

  $response->assertStatus(422);
  $this->assertDatabaseMissing('books', ['title' => 'Invalid Price Book']);
});

it('can update a book with valid data', function () {
  $author = Author::factory()->create();
  $book = Book::factory()->create(['author_id' => $author->id]);

  $updatedData = [
    'title' => 'Updated Title',
    'description' => 'Updated Description',
    'price' => 150,
  ];

  $response = $this->putJson("/api/books/{$book->id}", $updatedData);

  $response->assertStatus(200)
    ->assertJsonFragment(['title' => 'Updated Title']);

  $this->assertDatabaseHas('books', ['title' => 'Updated Title']);
});

it('returns 404 when updating non-existent book', function () {
  $updatedData = [
    'title' => 'Updated Title',
    'description' => 'Updated Description',
    'price' => 150,
  ];

  $response = $this->putJson('/api/books/9999', $updatedData);
  $response->assertStatus(404);
});

it('can soft delete a book', function () {
  $book = Book::factory()->create();

  $response = $this->patchJson("/api/books/{$book->id}", ['isDeleted' => 1]);

  $response->assertOk();
  $this->assertDatabaseHas('books', ['id' => $book->id, 'isDeleted' => 1]);
});

it('returns 404 when deleting non-existent book', function () {
  $response = $this->patchJson('/api/books/9999', ['isDeleted' => 1]);
  $response->assertStatus(404);
});
