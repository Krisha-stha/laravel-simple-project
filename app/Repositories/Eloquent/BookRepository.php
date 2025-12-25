<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\BookRepositoryInterface;

class BookRepository implements BookRepositoryInterface
{
    public function getAll(): Collection
    {
        return Book::with('author')->latest()->get();
    }

    public function find(int $id): ?Book
    {
        return Book::find($id);
    }

    public function create(array $data): Book
    {
        return Book::create($data);
    }

    public function update(int $id, array $data): Book
    {
        $book = $this->find($id);

        if (!$book) {
            throw new \RuntimeException('Book not found');
        }

        $book->update($data);

        return $book;
    }

    public function delete(int $id): bool
    {
        $book = $this->find($id);

        if (!$book) {
            throw new \RuntimeException('Book not found');
        }

        return $book->delete();
    }
}
