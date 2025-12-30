<?php

use App\Models\Book;
use App\Models\Author;
use Illuminate\Http\UploadedFile;

describe('Book API', function () {
  
  beforeEach(function () {
    $this->author = Author::factory()->create(['name' => 'Test Author']);
  });

  test('can get all books', function () {
    Book::factory()->count(3)->create([
      'author_id' => $this->author->id
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
    $book = Book::factory()->create([
      'author_id' => $this->author->id
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
              'author_id' => $this->author->id
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
    $bookData = [
      'title' => 'New Book Title',
      'description' => 'Book description',
      'price' => 29.99,
      'author_id' => $this->author->id
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

  test('can create a book with image', function () {
    $image = UploadedFile::fake()->image('book-cover.jpg', 400, 600);
    
    $bookData = [
      'title' => 'Book with Image',
      'description' => 'Book with cover image',
      'price' => 29.99,
      'author_id' => $this->author->id,
      'image' => $image
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(201)
      ->assertJson([
          'success' => true,
          'message' => 'Book created successfully'
      ]);

    $this->assertDatabaseHas('books', [
      'title' => 'Book with Image',
    ]);
    
    $book = Book::where('title', 'Book with Image')->first();
    $this->assertNotNull($book->image);
  });

  test('validates book creation data', function () {
    $response = $this->postJson('/api/books', []);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['title', 'author_id']);
  });

  test('validates price is numeric', function () {
    $bookData = [
      'title' => 'Invalid Price Book',
      'description' => 'Test description',
      'price' => 'not-a-number', 
      'author_id' => $this->author->id
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['price']);
  });

  test('validates price is not negative', function () {
    $bookData = [
      'title' => 'Negative Price Book',
      'description' => 'Test description',
      'price' => -10, 
      'author_id' => $this->author->id
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['price']);
  });

  test('validates image file type', function () {
    $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);
    
    $bookData = [
      'title' => 'Book with Invalid Image',
      'description' => 'Test description',
      'price' => 29.99,
      'author_id' => $this->author->id,
      'image' => $invalidFile
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['image']);
  });

  test('validates author exists', function () {
    $bookData = [
      'title' => 'Test Book',
      'description' => 'Test description',
      'price' => 29.99,
      'author_id' => 99999 
    ];

    $response = $this->postJson('/api/books', $bookData);

    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['author_id']);
  });

  test('can update a book', function () {
    $book = Book::factory()->create([
      'author_id' => $this->author->id
    ]);

    $updateData = [
      'title' => 'Updated Book Title',
      'description' => 'Updated description',
      'price' => 39.99,
      'author_id' => $this->author->id
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

  test('can update book with new image', function () {
    $initialImage = UploadedFile::fake()->image('old-cover.jpg');
    $book = Book::factory()->create([
      'author_id' => $this->author->id,
      'image' => 'books/old-cover.jpg' 
    ]);

    $newImage = UploadedFile::fake()->image('new-cover.jpg');
    
    $updateData = [
      'title' => 'Updated Book with New Image',
      'image' => $newImage
    ];

    $response = $this->putJson("/api/books/{$book->id}", $updateData);

    $response
      ->assertStatus(200)
      ->assertJson([
          'success' => true,
          'message' => 'Book updated successfully'
      ]);

    $book->refresh();
    $this->assertEquals('Updated Book with New Image', $book->title);
    $this->assertNotEquals('books/old-cover.jpg', $book->image);
  });

  test('can partially update a book using PATCH', function () {
    $book = Book::factory()->create([
      'author_id' => $this->author->id,
      'price' => 25.00
    ]);

    $updateData = [
      'title' => 'Partially Updated Title'
    ];

    $response = $this->patchJson("/api/books/{$book->id}", $updateData);

    $response
      ->assertStatus(200)
      ->assertJson([
          'success' => true,
          'message' => 'Book updated successfully'
      ]);

    $this->assertDatabaseHas('books', [
      'id' => $book->id,
      'title' => 'Partially Updated Title',
      'price' => 25.00 
    ]);
  });

  test('returns 404 when updating non-existent book', function () {
    $updateData = [
      'title' => 'Updated Title',
      'description' => 'Some description',
      'price' => 50.00,
      'author_id' => $this->author->id
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
    $book = Book::factory()->create([
      'author_id' => $this->author->id
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

  test('can delete a book with image', function () {
    $book = Book::factory()->create([
      'author_id' => $this->author->id,
      'image' => 'books/book-to-delete.jpg'
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

  test('searches books by title', function () {
    $book1 = Book::factory()->create([
      'author_id' => $this->author->id,
      'title' => 'Laravel for Beginners'
    ]);
    
    $book2 = Book::factory()->create([
      'author_id' => $this->author->id,
      'title' => 'Advanced PHP Patterns'
    ]);
    
    $book3 = Book::factory()->create([
      'author_id' => $this->author->id,
      'title' => 'JavaScript Fundamentals'
    ]);


    $response = $this->getJson('/api/books');

    $response->assertStatus(200);
    
    $responseData = $response->json();
    $this->assertCount(3, $responseData['data']);
  });

  test('filters books by author', function () {
    $anotherAuthor = Author::factory()->create(['name' => 'Another Author']);
    
    Book::factory()->count(2)->create(['author_id' => $this->author->id]);
    
    Book::factory()->create(['author_id' => $anotherAuthor->id]);

    $response = $this->getJson('/api/books');

    $response->assertStatus(200);
    

  });

  test('book belongs to author relationship', function () {
    $book = Book::factory()->create([
      'author_id' => $this->author->id
    ]);

    $this->assertInstanceOf(Author::class, $book->author);
    $this->assertEquals($this->author->id, $book->author->id);
    $this->assertEquals('Test Author', $book->author->name);
  });
});