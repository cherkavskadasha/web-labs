<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\BookIssueController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    
    // Authors
    Route::get('/authors', [AuthorController::class, 'getAuthors']);
    Route::get('/authors/{id}', [AuthorController::class, 'getAuthorById']);
    Route::post('/authors', [AuthorController::class, 'createAuthor']);
    Route::patch('/authors/{id}', [AuthorController::class, 'updateAuthorById']);
    Route::delete('/authors/{id}', [AuthorController::class, 'deleteAuthorById']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/categories/{id}', [CategoryController::class, 'getCategoryById']);
    Route::post('/categories', [CategoryController::class, 'createCategory']);
    Route::patch('/categories/{id}', [CategoryController::class, 'updateCategoryById']);
    Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategoryById']);

    // Books
    Route::get('/books', [BookController::class, 'getBooks']);
    Route::get('/books/{id}', [BookController::class, 'getBookById']);
    Route::post('/books', [BookController::class, 'createBook']);
    Route::patch('/books/{id}', [BookController::class, 'updateBookById']);
    Route::delete('/books/{id}', [BookController::class, 'deleteBookById']);

    // Readers
    Route::get('/readers', [ReaderController::class, 'getReaders']);
    Route::get('/readers/{id}', [ReaderController::class, 'getReaderById']);
    Route::post('/readers', [ReaderController::class, 'createReader']);
    Route::patch('/readers/{id}', [ReaderController::class, 'updateReaderById']);
    Route::delete('/readers/{id}', [ReaderController::class, 'deleteReaderById']);

    // Book Issues
    Route::get('/book-issues', [BookIssueController::class, 'getBookIssues']);
    Route::get('/book-issues/{id}', [BookIssueController::class, 'getBookIssueById']);
    Route::post('/book-issues', [BookIssueController::class, 'createBookIssue']);
    Route::patch('/book-issues/{id}', [BookIssueController::class, 'updateBookIssueById']);
    Route::delete('/book-issues/{id}', [BookIssueController::class, 'deleteBookIssueById']);
    
});