<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
  public function index() {
    $authors = Author::all();
    return view('authors.index', compact('authors'));
  }

  public function store(Request $request) {
    Author::create($request->only('name'));
    return redirect()->back();
  }
}
