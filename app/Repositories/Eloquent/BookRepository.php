<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;

class BookRepository
{
    public function getAll()
    {
        return Book::with('author')->latest()->get();
    }

    public function create(array $data): Book
    {
        return Book::create($data);
    }
}
