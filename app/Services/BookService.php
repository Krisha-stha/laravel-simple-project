<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\BookRepositoryInterface;

class BookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository
    ) {}

    public function getAllBooks(): Collection
    {
        return $this->bookRepository->getAll();
    }

    public function getBookById(int $id): ?Book
    {
        return $this->bookRepository->find($id);
    }

    public function store(array $data, Request $request): Book
    {
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        return $this->bookRepository->create($data);
    }

    public function update(int $id, array $data, Request $request): ?Book
    {
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        return $this->bookRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->bookRepository->delete($id);
    }
}