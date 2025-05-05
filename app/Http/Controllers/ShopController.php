<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function index()
    {
        try {
            $shops = Shop::with('products')->get();
            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Berhasil mengambil semua data toko',
                'data' => $shops
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'pemilik' => 'required',
            'alamat' => 'required',
            'foto' => 'required|string',
            'deskripsi' => 'nullable'
        ], [
            'required' => ':attribute harus diisi'
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
                // Get file extension
                $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

                // Create image name
                $imageName = str_replace(' ', '_', $validated['nama']) . '.' . $extension;

                // Create directory if it doesn't exist
                if (!file_exists(public_path('photos/shops'))) {
                    mkdir(public_path('photos/shops'), 0777, true);
                }

                // Decode and save image
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
                file_put_contents(public_path('photos/shops/') . $imageName, $image);

                $validated['foto'] = 'photos/shops/' . $imageName;
            }

            $shop = Shop::create($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Yeay! Toko baru berhasil ditambahkan',
                'data' => $shop
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menambahkan toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $shop = Shop::with('products')->find($id);

            if (!$shop) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Toko yang kamu cari tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Data toko berhasil ditemukan',
                'data' => $shop
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::find($id);

        if (!$shop) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Toko yang mau diubah tidak ditemukan',
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'pemilik' => 'required',
            'alamat' => 'required',
            'foto' => 'nullable|string',
            'deskripsi' => 'nullable'
        ], [
            'required' => ':attribute harus diisi'
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
                if ($shop->foto && file_exists(public_path($shop->foto))) {
                    unlink(public_path($shop->foto));
                }

                // Get file extension
                $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

                // Create image name
                $imageName = str_replace(' ', '_', $validated['nama']) . '.' . $extension;

                // Decode and save image
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
                file_put_contents(public_path('photos/shops/') . $imageName, $image);

                $validated['foto'] = 'photos/shops/' . $imageName;
            }

            $shop->update($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Toko berhasil diperbarui',
                'data' => $shop->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat memperbarui toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $shop = Shop::find($id);

            if (!$shop) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Toko yang mau dihapus tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Delete shop photo if exists
            if ($shop->foto && file_exists(public_path($shop->foto))) {
                unlink(public_path($shop->foto));
            }

            // Delete all product photos for this shop
            foreach ($shop->products as $product) {
                if ($product->foto && file_exists(public_path($product->foto))) {
                    unlink(public_path($product->foto));
                }
            }

            $shop->delete(); // This will also delete related products due to cascade

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Toko berhasil dihapus',
                'data' => null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menghapus toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getProducts($id)
    {
        try {
            $shop = Shop::with('products')->find($id);

            if (!$shop) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Toko yang kamu cari tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Berhasil mengambil data produk toko',
                'data' => $shop->products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data produk toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
