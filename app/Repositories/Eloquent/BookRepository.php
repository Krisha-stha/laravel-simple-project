<?php

namespace App\Repositories\Eloquent;

use App\Models\Book;
use App\Repositories\Interfaces\BookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BookRepository implements BookRepositoryInterface
{
  private const CACHE_DURATION = 3600;
  
  private const CACHE_ALL_BOOKS = 'books.all';
  private const CACHE_BOOK_PREFIX = 'book.';
  private const CACHE_STATS = 'books.statistics';

  private function baseQuery()
  {
      return Book::with(['author' => function ($query) {
          $query->select(['id', 'name', 'email', 'bio']);
      }]);
  }

  public function getAll(): Collection
  {
      return Cache::remember(self::CACHE_ALL_BOOKS, self::CACHE_DURATION, function () {
          return $this->baseQuery()
              ->whereNull('deleted_at')
              ->latest()
              ->get();
      });
  }

  public function paginate(int $perPage = 15): LengthAwarePaginator
  {
      return $this->baseQuery()
          ->whereNull('deleted_at')
          ->latest()
          ->paginate($perPage);
  }

  public function find(int $id): ?Book
  {
      $cacheKey = self::CACHE_BOOK_PREFIX . $id;
      
      return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($id) {
          return $this->baseQuery()->find($id);
      });
  }

  public function findOrFail(int $id): Book
  {
      $book = $this->find($id);
      
      if (!$book) {
          Log::warning('Book not found attempt', [
              'book_id' => $id,
          ]);
          
          throw new ModelNotFoundException(
              "Book with ID {$id} not found. Please check the ID and try again."
          );
      }
      
      return $book;
  }

  public function create(array $data): Book
  {
      try {
          $book = Book::create($data);
          
          $this->clearCaches();
          
          Log::info('Book created successfully', [
              'book_id' => $book->id,
              'title' => $book->title,
              'author_id' => $book->author_id,
              'created_at' => now()->toDateTimeString()
          ]);
          
          return $book->load(['author']);
          
      } catch (\Exception $e) {
          Log::error('Book creation failed', [
              'data_keys' => array_keys($data), 
              'error' => $e->getMessage(),
              'file' => $e->getFile(),
              'line' => $e->getLine()
          ]);
          
          throw new \RuntimeException(
              'Unable to create book. Please check the data and try again.'
          );
      }
  }

  public function update(int $id, array $data): Book
  {
      $book = $this->findOrFail($id);
      
      try {
          $oldData = $book->toArray();
          $book->update($data);
          
          $this->clearCaches($id);
          
          Log::info('Book updated successfully', [
              'book_id' => $id,
              'updated_at' => now()->toDateTimeString()
          ]);
          
          return $book->refresh()->load(['author']);
          
      } catch (\Exception $e) {
          Log::error('Book update failed', [
              'book_id' => $id,
              'error' => $e->getMessage(),
              'file' => $e->getFile(),
              'line' => $e->getLine()
          ]);
          
          throw new \RuntimeException(
              'Unable to update book. Please check the data and try again.'
          );
      }
  }

  public function delete(int $id): bool
  {
      $book = $this->findOrFail($id);
      
      try {
          Log::info('Book soft deleted', [
              'book_id' => $id,
              'book_title' => $book->title,
              'deleted_at' => now()->toDateTimeString()
          ]);
          
          $result = $book->delete();
          
          $this->clearCaches($id);
          
          return $result;
          
      } catch (\Exception $e) {
          Log::error('Book deletion failed', [
              'book_id' => $id,
              'error' => $e->getMessage(),
              'file' => $e->getFile(),
              'line' => $e->getLine()
          ]);
          
          throw new \RuntimeException(
              'Failed to delete book. Please try again or contact support.'
          );
      }
  }

  public function search(string $query, int $perPage = 15): LengthAwarePaginator
  {
      return $this->baseQuery()
          ->where(function ($q) use ($query) {
              $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhereHas('author', function ($authorQuery) use ($query) {
                    $authorQuery->where('name', 'LIKE', "%{$query}%");
                });
          })
          ->whereNull('deleted_at')
          ->latest()
          ->paginate($perPage);
  }

  public function findByAuthor(int $authorId, int $perPage = 15): LengthAwarePaginator
  {
      return $this->baseQuery()
          ->where('author_id', $authorId)
          ->whereNull('deleted_at')
          ->latest()
          ->paginate($perPage);
  }

  public function getFeaturedBooks(int $perPage = 15): LengthAwarePaginator
  {
      return $this->baseQuery()
          ->where('is_featured', true)
          ->whereNull('deleted_at')
          ->latest()
          ->paginate($perPage);
  }
  
  public function getTrashedBooks(int $perPage = 15): LengthAwarePaginator
  {
      return Book::with(['author'])
          ->onlyTrashed()
          ->latest()
          ->paginate($perPage);
  }
  

  public function restore(int $id): bool
  {
      $book = Book::withTrashed()->find($id);
      
      if (!$book) {
          throw new ModelNotFoundException("Book with ID {$id} not found");
      }
      
      Log::info('Book restored from trash', [
          'book_id' => $id,
          'restored_at' => now()->toDateTimeString()
      ]);
      
      $result = $book->restore();
      
      $this->clearCaches($id);
      
      return $result;
  }

  public function forceDelete(int $id): bool
  {
      $book = Book::withTrashed()->find($id);
      
      if (!$book) {
          throw new ModelNotFoundException("Book with ID {$id} not found");
      }
      
      Log::warning('Book permanently deleted', [
          'book_id' => $id,
          'book_title' => $book->title,
          'deleted_at' => now()->toDateTimeString()
      ]);
      
      $result = $book->forceDelete();
      
      $this->clearCaches($id);
      
      return $result;
  }

  public function getBooksByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): LengthAwarePaginator
  {
      return $this->baseQuery()
          ->whereBetween('price', [$minPrice, $maxPrice])
          ->whereNull('deleted_at')
          ->latest()
          ->paginate($perPage);
  }

  public function bulkUpdateFeatured(array $bookIds, bool $isFeatured): int
  {
      $result = Book::whereIn('id', $bookIds)
          ->update([
              'is_featured' => $isFeatured, 
              'updated_at' => now()
          ]);
      
      foreach ($bookIds as $id) {
          Cache::forget(self::CACHE_BOOK_PREFIX . $id);
      }
      Cache::forget(self::CACHE_ALL_BOOKS);
      Cache::forget(self::CACHE_STATS);
      
      Log::info('Bulk featured status update', [
          'book_ids' => $bookIds,
          'is_featured' => $isFeatured,
          'updated_count' => $result,
          'updated_at' => now()->toDateTimeString()
      ]);
      
      return $result;
  }

  public function getStatistics(): array
  {
      return Cache::remember(self::CACHE_STATS, self::CACHE_DURATION, function () {
          $latestBook = Book::latest()->first();
          $oldestBook = Book::oldest()->first();
          
          return [
              'total_books' => Book::count(),
              'active_books' => Book::whereNull('deleted_at')->count(),
              'featured_books' => Book::where('is_featured', true)->count(),
              'deleted_books' => Book::onlyTrashed()->count(),
              'average_price' => round(Book::avg('price') ?? 0, 2),
              'max_price' => round(Book::max('price') ?? 0, 2),
              'min_price' => round(Book::min('price') ?? 0, 2),
              'total_authors' => Book::distinct('author_id')->count('author_id'),
              'latest_book' => $latestBook ? [
                  'id' => $latestBook->id,
                  'title' => $latestBook->title,
                  'created_at' => $latestBook->created_at
              ] : null,
              'oldest_book' => $oldestBook ? [
                  'id' => $oldestBook->id,
                  'title' => $oldestBook->title,
                  'created_at' => $oldestBook->created_at
              ] : null,
          ];
      });
  }


  public function getBooksByMonth(int $year = null): Collection
  {
      $year = $year ?? date('Y');
      
      return Book::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
          ->whereYear('created_at', $year)
          ->whereNull('deleted_at')
          ->groupBy('month')
          ->orderBy('month')
          ->get()
          ->mapWithKeys(function ($item) {
              return [
                  date('F', mktime(0, 0, 0, $item->month, 1)) => $item->count
              ];
          });
  }


  private function clearCaches(int $bookId = null): void
  {
      Cache::forget(self::CACHE_ALL_BOOKS);
      Cache::forget(self::CACHE_STATS);
      
      if ($bookId) {
          Cache::forget(self::CACHE_BOOK_PREFIX . $bookId);
      }
  }
}