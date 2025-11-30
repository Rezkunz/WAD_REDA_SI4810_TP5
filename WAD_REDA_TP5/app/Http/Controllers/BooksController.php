<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use App\Http\Resources\BookResource;

class BooksController extends Controller
{
    /**
     * ==========1===========
     * Tampilkan daftar semua buku
     */
    public function index()
    {
        $book = Book::all();
        return BookResource::collection($book);
    }

    /**
     * ==========2===========
     * Simpan buku baru ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string',
            'published_year' => 'required|digits:4',
            'is_available' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check your request',
                'errors' => $validator->errors()
            ], 422);
        }
        $book = Book::create($validator->validate());
        
        return (new BookResource($book))
                    ->additional(['message' => 'Buku Telah Ditambahkan'])
                    ->response()
                    ->setStatusCode(201);
    }

    /**
     * =========3===========
     * Tampilkan detail buku tertentu.
     */
    public function show(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        return new BookResource($book);
    }

    /**
     * =========4===========
     * Fungsi untuk memperbarui data buku tertentu
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string',
            'published_year' => 'required|digits:4',
            'is_available' => 'required|boolean',
        ]);

        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message'=>'Buku Tidak Ditemukan'], 404);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check your request',
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($validator->validate());
        return (new BookResource($book))
                    ->additional(['message' => 'Buku Telah Diupdate'])
                    ->response()
                    ->setStatusCode(200);
    }

    /**
     * =========5===========
     * Hapus buku tertentu dari penyimpanan.
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);
         if (!$book) {
            return response()->json(['message'=>'Buku Tidak Ditemukan'], 404);
        }

        $book->delete();
        return response()->json(['message' => 'Buku Berhasil Dihapus'], 200);
    }

    /**
     * =========6===========
     * Ubah status ketersediaan buku (ubah field is_available)
     */
    public function borrowReturn(string $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message'=>'Buku Tidak Ditemukan'], 404);
        }

        $book->is_available = !$book->is_available;
        $book->save();

        return response()->json([
        'message' => $book->is_available 
            ? 'Buku berhasil dikembalikan' 
            : 'Buku berhasil dipinjam',
        'data' => $book
        ], 200);
    }
}
