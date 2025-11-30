<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Dotenv\Parser\Value;

class BooksController extends Controller
{
    /**
     * ==========1===========
     * Tampilkan daftar semua buku
     */
    public function index()
    {
        $books = Book::all();
        return BookResource::collection($books);
    }

    /**
     * ==========2===========
     * Simpan buku baru ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_year' => 'required|digits:4|integer',
            'is_available' => 'required|boolean'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'cek ulang requestmu',
                'error' => $validator->errors()
            ], 422);
        }

        $books = Book::create($validator->validated());

        return (new BookResource($books))
            ->additional(['message' => 'buku berhasil ditambahkan'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * =========3===========
     * Tampilkan detail buku tertentu.
     */
    public function show(string $id)
    {
        $books = Book::find($id);
        if (!$books) {
            return response()->json([
                'message' => 'buku yang kamu cari tidak ada'
            ], 404);
        }
        return new BookResource($books);
    }

    /**
     * =========4===========
     * Fungsi untuk memperbarui data buku tertentu
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_year' => 'required|digits:4|integer',
            'is_available' => 'required|boolean'

        ]);
        $books = Book::find($id);
        if (!$books) {
            return response()->json([
                'message' => 'buku tidak ditemukan'
            ], 404);
        }
        if ($validator->fails()) {
            return response()->json([
                'message' => 'tolong cek request mu',
                'errors' => $validator->errors()
            ], 422);
        }
        $books->update($validator->validated());

        return (new BookResource($books))
            ->additional(['message' => 'buku berhasil diupdate'])
            ->response()
            ->setStatusCode(201);
    }


    /**
     * =========5===========
     * Hapus buku tertentu dari penyimpanan.
     */
    public function destroy(string $id)
    {
        $books = Book::find($id);
        if (!$books) {
            return response()->json([
                'message' => 'buku tidak ditemukan'
            ], 404);
        }
        $books->delete();
        return response()->json([
            'message' => 'buku berhasil dihapus'
        ], 200);
    }

    /**
     * =========6===========
     * Ubah status ketersediaan buku (ubah field is_available)
     */
    public function borrowReturn(string $id)
    {
        $books = Book::find($id);

        $books->is_available = !$books->is_available;
        $books->save();

        $statusMessage = $books->is_available
            ? 'buku berhasil dikembalikan'
            : 'buku berhasil dipinjam';

        return (new BookResource($books))
            ->additional(['message' => $statusMessage])
            ->response()
            ->setStatusCode(200);
    }
}
