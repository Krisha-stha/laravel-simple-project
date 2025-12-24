<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('author')->latest()->get();
        return view('books.index', compact('books'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'price'       => 'required|numeric|min:0',
                'image'       => 'nullable|string|max:255', // for filename
                'author_id'   => 'required|exists:authors,id',
            ]);

            Book::create($validated);

            return redirect()->route('books.index')->with('success', 'Book added successfully');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Book create failed: '.$e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }
}
