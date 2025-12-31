<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book');  
        
        return [
            'title'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('books')->ignore($bookId), 
            ],
            'description' => 'nullable|string|max:1000',
            'price'       => 'required|numeric|min:0|max:10000',  
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'author_id'   => 'required|exists:authors,id',
            'is_featured' => 'nullable|boolean',  
        ];
    }
    
    // ADDED: Custom messages
    public function messages(): array
    {
        return [
            'title.unique' => 'A book with this title already exists.',
            'price.max' => 'Price cannot exceed $10,000.',
        ];
    }
}