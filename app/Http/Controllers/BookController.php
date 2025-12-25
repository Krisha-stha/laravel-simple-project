<?php

namespace App\Http\Controllers;

use App\Services\BookService;
use App\Http\Requests\BookRequest;

class BookController extends Controller
{
    public function __construct(
        private readonly BookService $bookService
    ) {}

    public function index()
    {
        return view('books.index', [
            'books' => $this->bookService->getAllBooks()
        ]);
    }

    public function store(BookRequest $request)
    {
        $this->bookService->store($request->validated(), $request);

        return redirect()
            ->route('books.index')
            ->with('success', 'Book added successfully');
    }

    public function edit(int $id)
    {
        return view('books.edit', [
            'book' => $this->bookService->getBookById($id)
        ]);
    }

    public function update(BookRequest $request, int $id)
    {
        $this->bookService->update($id, $request->validated(), $request);

        return redirect()
            ->route('books.index')
            ->with('success', 'Book updated successfully');
    }

    public function destroy(int $id)
    {
        $this->bookService->delete($id);

        return redirect()
            ->route('books.index')
            ->with('success', 'Book deleted successfully');
    }
}
