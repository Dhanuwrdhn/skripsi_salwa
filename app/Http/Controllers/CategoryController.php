<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::with('products')->get();
            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Berhasil mengambil semua data kategori',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|unique:categories,nama',
            'deskripsi' => 'nullable'
        ], [
            'required' => ':attribute harus diisi',
            'unique' => ':attribute sudah ada'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ada kesalahan dalam pengisian form',
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $category = Category::create($validator->validated());

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Yeay! Kategori baru berhasil ditambahkan',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menambahkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::with('products')->find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Kategori yang kamu cari tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Data kategori berhasil ditemukan',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Kategori yang mau diubah tidak ditemukan',
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|unique:categories,nama,' . $id,
            'deskripsi' => 'nullable'
        ], [
            'required' => ':attribute harus diisi',
            'unique' => ':attribute sudah ada'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ada kesalahan dalam pengisian form',
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $category->update($validator->validated());

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Kategori berhasil diperbarui',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat memperbarui kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Kategori yang mau dihapus tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($category->products()->count() > 0) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Kategori tidak bisa dihapus karena masih digunakan produk',
                    'error' => 'Kategori masih digunakan'
                ], 422);
            }

            DB::beginTransaction();

            $category->delete();

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Kategori berhasil dihapus',
                'data' => null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menghapus kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
