<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Book extends Model
{
    use HasFactory, SoftDeletes;  
    
    protected $fillable = [
        'title',
        'description',
        'price',
        'image',
        'author_id',
        'is_featured', 
    ];
    
    protected $casts = [
        'price' => 'float',
        'is_featured' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopeExpensive($query, $minPrice = 50)
    {
        return $query->where('price', '>', $minPrice);
    }
}