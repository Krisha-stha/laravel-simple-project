@extends('layout')
@section('content')
<h2>Books</h2>
{{-- Show Validation Errors --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
{{-- Show Success Message --}}
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
{{-- Add Book Form --}}
<form action="{{ route('books.store') }}" method="POST" class="mb-3" enctype="multipart/form-data">
    @csrf
    <input
        type="text"
        name="title"
        placeholder="Book Title"
        class="form-control mb-2 w-50"
        value="{{ old('title') }}"
        required
    >
    <textarea
        name="description"
        placeholder="Description"
        class="form-control mb-2 w-50"
    >{{ old('description') }}</textarea>
    <input
        type="number"
        step="0.01"
        name="price"
        placeholder="Price"
        class="form-control mb-2 w-25"
        value="{{ old('price') }}"
        required
    >
    <input type="file" name="image" class="form-control mb-2 w-50" accept="image/*">
    <select name="author_id" class="form-select mb-2 w-25" required>
        <option value="">Select Author</option>
        @foreach(App\Models\Author::all() as $author)
            <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>
                {{ $author->name }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Add Book</button>
</form>

{{-- Books Table --}}
<table class="table mt-4 w-100">
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Description</th>
            <th>Price</th>
            <th>Image</th>
            <th>Actions</th> {{-- New column --}}
        </tr>
    </thead>
    <tbody>
        @forelse($books as $book)
            <tr>
                <td>{{ $book->title }}</td>
                <td>{{ $book->author->name ?? 'N/A' }}</td>
                <td>{{ $book->description ?? '-' }}</td>
                <td>${{ number_format($book->price, 2) }}</td>
                <td>
                    @if($book->image)
                        <img src="{{ asset('storage/'.$book->image) }}" width="80">
                    @else
                        -
                    @endif
                </td>
                <td>
                    <a href="{{ route('books.edit', $book->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">No books found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
