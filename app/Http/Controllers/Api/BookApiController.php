<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookService;
use App\Http\Requests\BookRequest;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class BookApiController extends Controller
{
  use ApiResponse;

  public function __construct(private readonly BookService $bookService) {}

  public function index(): JsonResponse
  {
    $books = $this->bookService->getAllBooks();
    return $this->successResponse(data: $books, message: 'Books retrieved successfully');
  }

  public function show(int $id): JsonResponse
  {
    $book = $this->bookService->getBookById($id);

    if (!$book) {
      return $this->errorResponse(message: 'Book not found', status: 404);
    }

    return $this->successResponse(data: $book, message: 'Book retrieved successfully');
  }

  public function store(BookRequest $request): JsonResponse
  {
    $book = $this->bookService->store($request->validated(), $request);

    return $this->successResponse(data: $book, message: 'Book created successfully', status: 201);
  }

  public function update(BookRequest $request, int $id): JsonResponse
  {
    $book = $this->bookService->update($id, $request->validated(), $request);

    if (!$book) {
      return $this->errorResponse(message: 'Book not found', status: 404);
    }

    return $this->successResponse(data: $book, message: 'Book updated successfully');
  }

  public function destroy(int $id): JsonResponse
  {
    $deleted = $this->bookService->delete($id);

    if (!$deleted) {
      return $this->errorResponse(message: 'Book not found', status: 404);
    }

    return $this->successResponse(data: [], message: 'Book deleted successfully');
  }
}
