<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $books = Book::with('author')->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $book->load('author')
        ]);
    }

    public function store(BookRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }
        
        $book = Book::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book
        ], 201);
    }

    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $data = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image) {
                \Storage::disk('public')->delete($book->image);
            }
            
            $data['image'] = $request->file('image')->store('books', 'public');
        }
        
        $book->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book
        ]);
    }

    public function destroy(Book $book): JsonResponse
    {
        // Delete image if exists
        if ($book->image) {
            \Storage::disk('public')->delete($book->image);
        }
        
        $book->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully'
        ]);
    }
}