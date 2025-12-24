<?php

namespace App\Http\Controllers;

use App\Services\BookService;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\BookRepository;

class BookController extends Controller
{
    // protected BookService $bookService;

    // public function __construct(BookService $bookService)
    // {
    //     $this->bookService = $bookService;
    // }

    public function __construct(protected BookService $bookService) {}

    public function index()
    {
        $books = $this->bookService->getAllBooks();
        return view('books.index', compact('books'));
    }

    public function store(Request $request)
    {
        try {
            $this->bookService->storeBook($request);
            return redirect()->route('books.index')->with('success', 'Book added successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
