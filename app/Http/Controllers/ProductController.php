<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Berhasil mengambil semua data produk',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'harga' => 'required|numeric',
            'nama_toko' => 'required',
            'foto' => 'required|string', // Changed to accept base64 string
            'kategori' => 'required',
            'description' => 'nullable'
        ], [
            'required' => ':attribute harus diisi',
            'numeric' => ':attribute harus berupa angka'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ada kesalahan dalam pengisian form',
                'error' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

           if ($request->foto) {
            // Get file extension (jpg, png, etc)
            $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

            // Create image name
            $date = Carbon::now()->format('Ymd');
            $imageName = str_replace(' ', '_', $validated['nama_toko']) . '_' .
                        $date . '_' .
                        str_replace(' ', '_', $validated['nama']) . '.' .
                        $extension;

            // Create directory if it doesn't exist
            if (!file_exists(public_path('photos/products'))) {
                mkdir(public_path('photos/products'), 0777, true);
            }

            // Decode and save image
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
            file_put_contents(public_path('photos/products/') . $imageName, $image);

            $validated['foto'] = 'photos/products/' . $imageName;
        }
            $product = Product::create($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Yeay! Produk baru berhasil ditambahkan',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menambahkan produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Produk yang kamu cari tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Data produk berhasil ditemukan',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Produk yang mau diubah tidak ditemukan',
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'harga' => 'required|numeric',
            'nama_toko' => 'required',
            'foto' => 'nullable|string', // Changed to accept base64 string
            'kategori' => 'required',
            'description' => 'nullable'
        ], [
            'required' => ':attribute harus diisi',
            'numeric' => ':attribute harus berupa angka'
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

            $validated = $validator->validated();

             if ($request->foto) {
        // Delete old photo if exists
        if ($product->foto && file_exists(public_path($product->foto))) {
            unlink(public_path($product->foto));
        }

        // Get file extension
        $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

        // Create image name
        $date = Carbon::now()->format('Ymd');
        $imageName = str_replace(' ', '_', $validated['nama_toko']) . '_' .
                    $date . '_' .
                    str_replace(' ', '_', $validated['nama']) . '.' .
                    $extension;

        // Make sure products directory exists
        if (!file_exists(public_path('photos/products'))) {
            mkdir(public_path('photos/products'), 0777, true);
        }

        // Decode and save image
        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
        file_put_contents(public_path('photos/products/') . $imageName, $image);

        $validated['foto'] = 'photos/products/' . $imageName;
    }

            $product->update($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Produk berhasil diperbarui',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat memperbarui produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Produk yang mau dihapus tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Delete photo if exists
            if ($product->foto && file_exists(public_path($product->foto))) {
                unlink(public_path($product->foto));
            }

            $product->delete();

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Produk berhasil dihapus',
                'data' => null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menghapus produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
