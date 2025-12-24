<?php

namespace App\Services;

use App\Repositories\Eloquent\BookRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookService
{
  // protected BookRepository $bookRepo;

  // public function __construct(BookRepository $bookRepo)
  // {
  //     $this->bookRepo = $bookRepo;
  // }

  public function __construct(protected BookRepositoryInterface $bookRepository){}

  public function getAllBooks()
  {
    return $this->bookRepo->getAll();
  }

  public function storeBook(Request $request)
  {
    try {
      $validated = $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'price'       => 'required|numeric|min:0',
        'image'       => 'nullable|string|max:255',
        'author_id'   => 'required|exists:authors,id',
      ]);

      return $this->bookRepo->create($validated);

    } catch (ValidationException $e) {
      throw $e;
    } catch (\Throwable $e) {
      Log::error('Book creation failed: ' . $e->getMessage());
      throw new \Exception('Something went wrong.');
    }
  }
}
