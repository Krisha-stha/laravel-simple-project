@extends('layout')
@section('content')
<h2>Edit Book</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="text" name="title" placeholder="Book Title" class="form-control mb-2 w-50" value="{{ old('title', $book->title) }}" required>

    <textarea name="description" placeholder="Description" class="form-control mb-2 w-50">{{ old('description', $book->description) }}</textarea>

    <input type="number" step="0.01" name="price" placeholder="Price" class="form-control mb-2 w-25" value="{{ old('price', $book->price) }}" required>

    @if($book->image)
        <img src="{{ asset('storage/'.$book->image) }}" width="80" class="mb-2">
    @endif
    <input type="file" name="image" class="form-control mb-2 w-50" accept="image/*">

    <select name="author_id" class="form-select mb-2 w-25" required>
        @foreach(App\Models\Author::all() as $author)
            <option value="{{ $author->id }}" {{ old('author_id', $book->author_id) == $author->id ? 'selected' : '' }}>
                {{ $author->name }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary">Update Book</button>
</form>
@endsection
