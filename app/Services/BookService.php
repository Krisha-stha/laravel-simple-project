<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; 

class BookService
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository
    ) {}

    public function getAllBooks(): Collection
    {
        return $this->bookRepository->getAll();
    }
    
    public function getPaginatedBooks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->paginate($perPage);
    }

    public function getBookById(int $id): ?Book
    {
        return $this->bookRepository->find($id);
    }
    
    public function getBookOrFail(int $id): Book
    {
        return $this->bookRepository->findOrFail($id);
    }

    public function store(array $data, Request $request): Book
    {
        if (!isset($data['is_featured'])) {
            $data['is_featured'] = false;
        }
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        return $this->bookRepository->create($data);
    }

    public function update(int $id, array $data, Request $request): ?Book
    {
        if (!isset($data['is_featured'])) {
            $data['is_featured'] = false;
        }
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        return $this->bookRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->bookRepository->delete($id);
    }
    
    public function searchBooks(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->search($query, $perPage);
    }
    
    public function getBooksByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->findByAuthor($authorId, $perPage);
    }
    
    public function getFeaturedBooks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->getFeaturedBooks($perPage);
    }
    
    public function getTrashedBooks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->getTrashedBooks($perPage);
    }
    
    public function restoreBook(int $id): bool
    {
        return $this->bookRepository->restore($id);
    }
    
    public function forceDeleteBook(int $id): bool
    {
        return $this->bookRepository->forceDelete($id);
    }
}