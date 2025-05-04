<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index()
    {
        try {
            $activities = Activity::all();
            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Berhasil mengambil semua data kegiatan',
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'deskripsi' => 'nullable',
            'tempat' => 'required',
            'foto' => 'required|string' // For base64 image
        ], [
            'required' => ':attribute harus diisi',
            'date' => ':attribute harus berupa tanggal',
            'after' => ':attribute harus setelah tanggal mulai'
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
                // Get file extension (jpg, png, etc)
                $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

                // Create image name using nama_kegiatan
                $imageName = str_replace(' ', '_', $validated['nama_kegiatan']) . '.' . $extension;

                // Create directory if it doesn't exist
                if (!file_exists(public_path('photos'))) {
                    mkdir(public_path('photos'), 0777, true);
                }

                // Decode and save image
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
                file_put_contents(public_path('photos/') . $imageName, $image);

                $validated['foto'] = 'photos/' . $imageName;
            }

            $activity = Activity::create($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Yeay! Kegiatan baru berhasil ditambahkan',
                'data' => $activity
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menambahkan kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Kegiatan yang kamu cari tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Data kegiatan berhasil ditemukan',
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Gagal mengambil data kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Kegiatan yang mau diubah tidak ditemukan',
                'error' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'deskripsi' => 'nullable',
            'tempat' => 'required',
            'foto' => 'nullable|string' // Optional for update
        ], [
            'required' => ':attribute harus diisi',
            'date' => ':attribute harus berupa tanggal',
            'after' => ':attribute harus setelah tanggal mulai'
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
                if ($activity->foto && file_exists(public_path($activity->foto))) {
                    unlink(public_path($activity->foto));
                }

                // Get file extension
                $extension = explode('/', explode(':', substr($request->foto, 0, strpos($request->foto, ';')))[1])[1];

                // Create image name using nama_kegiatan
                $imageName = str_replace(' ', '_', $validated['nama_kegiatan']) . '.' . $extension;

                // Decode and save image
                $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->foto));
                file_put_contents(public_path('photos/') . $imageName, $image);

                $validated['foto'] = 'photos/' . $imageName;
            }

            $activity->update($validated);

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Kegiatan berhasil diperbarui',
                'data' => $activity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat memperbarui kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json([
                    'status' => 'gagal',
                    'messages' => 'Ups! Kegiatan yang mau dihapus tidak ditemukan',
                    'error' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Delete photo if exists
            if ($activity->foto && file_exists(public_path($activity->foto))) {
                unlink(public_path($activity->foto));
            }

            $activity->delete();

            DB::commit();

            return response()->json([
                'status' => 'berhasil',
                'messages' => 'Kegiatan berhasil dihapus',
                'data' => null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'gagal',
                'messages' => 'Ups! Ada masalah saat menghapus kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
