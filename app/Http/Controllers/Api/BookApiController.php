<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookService;
use App\Http\Requests\BookRequest;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request; 

class BookApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BookService $bookService) {}

    public function index(Request $request): JsonResponse  
    {
        $perPage = $request->get('per_page', 15);  
        
        $books = $this->bookService->getAllBooks()
            ->paginate($perPage);  
        
        return $this->successResponse(
            [
                'books' => $books->items(),
                'pagination' => [
                    'total' => $books->total(),
                    'per_page' => $books->perPage(),
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                ]
            ],
            'Books retrieved successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $book = $this->bookService->getBookById($id);

        if (!$book) {
            return $this->errorResponse('Book not found', 404);
        }

        return $this->successResponse(
            $book->load('author')->toArray(),  
            'Book retrieved successfully'
        );
    }

    public function store(BookRequest $request): JsonResponse
    {
        $book = $this->bookService->store($request->validated(), $request);

        return $this->successResponse(
            $book->load('author')->toArray(),  
            'Book created successfully', 
            201
        );
    }

    public function update(BookRequest $request, int $id): JsonResponse
    {
        $book = $this->bookService->update($id, $request->validated(), $request);

        if (!$book) {
            return $this->errorResponse('Book not found', 404);
        }

        return $this->successResponse(
            $book->load('author')->toArray(),  
            'Book updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->bookService->delete($id);

        if (!$deleted) {
            return $this->errorResponse('Book not found', 404);
        }

        return $this->successResponse([], 'Book deleted successfully', 204);  
    }
}