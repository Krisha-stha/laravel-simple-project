<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class BookRepository implements BookRepositoryInterface
{
    public function getAll(): Collection
    {
        return Book::with(['author', 'genres'])->latest()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Book::with(['author', 'genres'])->latest()->paginate($perPage);
    }

    public function find(int $id): ?Book
    {
        return Book::with(['author', 'genres'])->find($id);
    }

    public function findOrFail(int $id): Book
    {
        $book = Book::with(['author', 'genres'])->find($id);
        
        if (!$book) {
            throw new ModelNotFoundException("Book with ID {$id} not found");
        }
        
        return $book;
    }

    public function create(array $data): Book
    {
        try {
            $book = Book::create($data);
            
            if (isset($data['genre_ids'])) {
                $book->genres()->sync($data['genre_ids']);
            }
            
            return $book->load(['author', 'genres']);
            
        } catch (\Exception $e) {
            Log::error('Failed to create book: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            
            throw new \RuntimeException('Failed to create book: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): Book
    {
        $book = $this->findOrFail($id);
        
        try {
            $book->update($data);
            
            if (isset($data['genre_ids'])) {
                $book->genres()->sync($data['genre_ids']);
            }
            
            return $book->refresh()->load(['author', 'genres']);
            
        } catch (\Exception $e) {
            Log::error('Failed to update book: ' . $e->getMessage(), [
                'book_id' => $id,
                'data' => $data,
                'exception' => $e
            ]);
            
            throw new \RuntimeException('Failed to update book: ' . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        $book = $this->findOrFail($id);
        
        try {
            // Detach relationships before deleting
            $book->genres()->detach();
            
            return $book->delete();
            
        } catch (\Exception $e) {
            Log::error('Failed to delete book: ' . $e->getMessage(), [
                'book_id' => $id,
                'exception' => $e
            ]);
            
            throw new \RuntimeException('Failed to delete book: ' . $e->getMessage());
        }
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Book::with(['author', 'genres'])
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhereHas('author', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        return Book::with(['author', 'genres'])
            ->where('author_id', $authorId)
            ->latest()
            ->paginate($perPage);
    }
}