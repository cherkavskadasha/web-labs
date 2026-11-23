<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\BookIssueController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/authors', [AuthorController::class, 'getAuthors']);
    Route::get('/authors/{id}', [AuthorController::class, 'getAuthorById']);

    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/categories/{id}', [CategoryController::class, 'getCategoryById']);

    Route::get('/books', [BookController::class, 'getBooks']);
    Route::get('/books/{id}', [BookController::class, 'getBookById']);

    Route::get('/readers', [ReaderController::class, 'getReaders']);
    Route::get('/readers/{id}', [ReaderController::class, 'getReaderById']);

    Route::get('/book-issues', [BookIssueController::class, 'getBookIssues']);
    Route::get('/book-issues/{id}', [BookIssueController::class, 'getBookIssueById']);

    Route::middleware('role:ROLE_MANAGER')->group(function () {
        Route::post('/authors', [AuthorController::class, 'createAuthor']);
        Route::patch('/authors/{id}', [AuthorController::class, 'updateAuthorById']);

        Route::post('/categories', [CategoryController::class, 'createCategory']);
        Route::patch('/categories/{id}', [CategoryController::class, 'updateCategoryById']);

        Route::post('/books', [BookController::class, 'createBook']);
        Route::patch('/books/{id}', [BookController::class, 'updateBookById']);

        Route::post('/readers', [ReaderController::class, 'createReader']);
        Route::patch('/readers/{id}', [ReaderController::class, 'updateReaderById']);

        Route::post('/book-issues', [BookIssueController::class, 'createBookIssue']);
        Route::patch('/book-issues/{id}', [BookIssueController::class, 'updateBookIssueById']);
    });

    Route::middleware('role:ROLE_ADMIN')->group(function () {
        Route::delete('/authors/{id}', [AuthorController::class, 'deleteAuthorById']);
        Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategoryById']);
        Route::delete('/books/{id}', [BookController::class, 'deleteBookById']);
        Route::delete('/readers/{id}', [ReaderController::class, 'deleteReaderById']);
        Route::delete('/book-issues/{id}', [BookIssueController::class, 'deleteBookIssueById']);
    });
});