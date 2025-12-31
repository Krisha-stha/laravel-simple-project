<?php

namespace App\Repositories\Interfaces;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface BookRepositoryInterface
{
    public function getAll(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Book;
    public function findOrFail(int $id): Book;
    public function create(array $data): Book;
    public function update(int $id, array $data): Book;
    public function delete(int $id): bool;
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
    public function findByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator;
    
    public function getFeaturedBooks(int $perPage = 15): LengthAwarePaginator;
    
    public function getTrashedBooks(int $perPage = 15): LengthAwarePaginator;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    
    public function getBooksByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): LengthAwarePaginator;
    public function bulkUpdateFeatured(array $bookIds, bool $isFeatured): int;
    public function getStatistics(): array;
    public function getBooksByMonth(int $year = null): Collection;
}