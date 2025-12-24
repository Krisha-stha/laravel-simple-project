@extends('layout')
@section('content')
<h2>Authors</h2>

<form action="{{ route('authors.store') }}" method="POST" class="mb-3">
  @csrf
  <input type="text" name="name" placeholder="Author Name" class="form-control w-50 d-inline" required>
  <button type="submit" class="btn btn-primary">Add Author</button>
</form>

<ul class="list-group w-50">
  @foreach($authors as $author)
      <li class="list-group-item">{{ $author->name }}</li>
  @endforeach
</ul>
@endsection
