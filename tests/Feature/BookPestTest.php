<?php

use App\Models\Book;
use App\Models\Author;

describe('Book API', function () {
  
  beforeEach(function () {
  });

  test('can get all books', function () {
    $author = Author::create([
      'name' => 'Test Author'
    ]);

    Book::factory()->count(3)->create([
      'author_id' => $author->id
    ]);

    $response = $this->getJson('/api/books');

    $response
      ->assertStatus(200)
      ->assertJsonStructure([
          'success',
          'data' => [
              '*' => ['id', 'title', 'author_id']
          ]
      ]);
  });

  test('can get a single book', function () {
    $author = Author::create([
      'name' => 'Test Author'
    ]);

    $book = Book::factory()->create([
      'author_id' => $author->id
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
              'author_id' => $author->id
          ]
      ]);
  });

  test('returns 404 for non-existent book', function () {
    $response = $this->getJson('/api/books/99999');

    $response
      ->assertStatus(404)
      ->assertJson([
          'success' => false,
          'message' => 'Book not found'
      ]);
  });

  test('can create a book', function () {
    $author = Author::create([
      'name' => 'Test Author'
    ]);

    $bookData = [
      'title' => 'New Book Title',
      'description' => 'Book description',
      'price' => 29.99,
      'author_id' => $author->id
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(201)
      ->assertJson([
          'success' => true,
          'message' => 'Book created successfully'
      ]);

    $this->assertDatabaseHas('books', [
      'title' => 'New Book Title'
    ]);
  });

  test('validates book creation data', function () {
    $response = $this->postJson('/api/books', []);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['title', 'author_id']);
  });

  test('can update a book', function () {
    $author = Author::create([
      'name' => 'Test Author'
    ]);

    $book = Book::factory()->create([
      'author_id' => $author->id
    ]);

    $updateData = [
      'title' => 'Updated Book Title',
      'description' => 'Updated description',
      'price' => 39.99,
      'author_id' => $author->id
    ];

    $response = $this->putJson("/api/books/{$book->id}", $updateData);

    $response
      ->assertStatus(200)
      ->assertJson([
          'success' => true,
          'message' => 'Book updated successfully'
      ]);

    $this->assertDatabaseHas('books', [
      'id' => $book->id,
      'title' => 'Updated Book Title'
    ]);
  });

  test('returns 404 when updating non-existent book', function () {
  $author = Author::create([
    'name' => 'Test Author'
  ]);

  $updateData = [
    'title' => 'Updated Title',
    'description' => 'Some description',
    'price' => 50.00,
    'author_id' => $author->id
  ];

  $response = $this->putJson('/api/books/99999', $updateData);

  $response
    ->assertStatus(404)
    ->assertJson([
      'success' => false,
      'message' => 'Book not found'
    ]);
  });

  test('can delete a book', function () {
    $author = Author::create([
      'name' => 'Test Author'
    ]);

    $book = Book::factory()->create([
      'author_id' => $author->id
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
  });

  test('returns 404 when deleting non-existent book', function () {
      $response = $this->deleteJson('/api/books/99999');

      $response
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Book not found'
        ]);
  });
});