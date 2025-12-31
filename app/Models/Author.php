<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;  // ADDED

class Author extends Model
{
    use HasFactory, SoftDeletes;  
    
    protected $fillable = ['name', 'bio', 'email'];  
    
    public function books()
    {
        return $this->hasMany(Book::class);
    }
    
    // ADDED: New method to get author's full info
    public function getFullInfoAttribute()
    {
        return "{$this->name} - {$this->email}";
    }
}