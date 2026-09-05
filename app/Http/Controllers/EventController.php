<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Kegiatan;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kegiatan = Kegiatan::with(['kandidat.mahasiswa.program_studi'])->withCount([
                'mahasiswa as total_mahasiswa',
                'mahasiswa as jumlah_pemilih' => function ($query) {
                    $query->whereNotNull('has_vote');
                }
            ])->get()->toResourceCollection();
        $programStudi = ProgramStudi::all();
        return Inertia::render('kegiatan/Index', ['kegiatan' => $kegiatan, 'programStudi' => $programStudi]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('events/Tambah');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000|max:2100',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
            'foto' => 'required|image|max:4096|mimes:jpeg,png,jpg,webp',
        ], [
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'waktu_selesai.after_or_equal' => 'Waktu selesai harus sama dengan atau setelah waktu mulai.',
            'foto.required' => 'Foto kegiatan wajib diisi.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 4MB.',
            'foto.mimes' => 'Foto harus berupa file dengan ekstensi jpeg, png, jpg, atau webp.',
        ]);

        try {
            // Store foto if exists
            $fotoPath = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoFile = $request->file('foto');
                // SECURITY: Use random name only — never trust client-supplied filename
                $fotoName = time() . '_' . Str::random(16) . '.' . $fotoFile->getClientOriginalExtension();

                $fotoDestination = Storage::disk('public')->path('foto-kegiatan');
                if (!file_exists($fotoDestination)) {
                    mkdir($fotoDestination, 0755, true);
                }

                Storage::disk('public')->putFileAs('foto-kegiatan', $fotoFile, $fotoName);
                $fotoPath = 'foto-kegiatan/' . $fotoName;
            }

            // Create new kegiatan record
            Kegiatan::create([
                'nama' => $validatedData['nama'],
                'ruang_lingkup' =>'fakultas',
                'tahun' => $validatedData['tahun'],
                'waktu_mulai' => $validatedData['waktu_mulai'],
                'waktu_selesai' => $validatedData['waktu_selesai'],
                'foto' => $fotoPath,
            ]);

            // Redirect with success message
            return to_route('events.index')->with('alert', ['type' => 'success', 'title' => 'Berhasil menambah kegiatan', 'message' => 'Data kegiatan pemilihan baru berhasil ditambah']);
        } catch (\Exception $e) {
            Log::error('[EVENT] Gagal membuat kegiatan', ['error' => $e->getMessage()]);
            return back()->with('alert', ['type' => 'error', 'title' => 'Kesalahan Penambahan Data', 'message' => 'Terjadi kesalahan saat membuat data kegiatan. Silakan coba lagi.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kegiatan = Kegiatan::findOrFail($id)->toResource();
        return Inertia::render('events/Edit', ['kegiatan' => $kegiatan]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate input data
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000|max:2100',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after_or_equal:waktu_mulai',
            'foto' => 'nullable|image|max:4096|mimes:jpeg,png,jpg,webp',
        ], [
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'waktu_selesai.after_or_equal' => 'Waktu selesai harus sama dengan atau setelah waktu mulai.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 4MB.',
            'foto.mimes' => 'Foto harus berupa file dengan ekstensi jpeg, png, jpg, atau webp.',
        ]);

        try {
            // Find the kegiatan to update
            $kegiatan = Kegiatan::findOrFail($id);

            // Handle foto upload
            $fotoPath = $kegiatan->foto;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }

                $fotoFile = $request->file('foto');
                // SECURITY: Use random name only — never trust client-supplied filename
                $fotoName = time() . '_' . Str::random(16) . '.' . $fotoFile->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('foto-kegiatan', $fotoFile, $fotoName);
                $fotoPath = 'foto-kegiatan/' . $fotoName;
            }

            // Update kegiatan
            $kegiatan->update([
                'nama' => $validatedData['nama'],
                'tahun' => $validatedData['tahun'],
                'waktu_mulai' => $validatedData['waktu_mulai'],
                'waktu_selesai' => $validatedData['waktu_selesai'],
                'foto' => $fotoPath,
            ]);

            // Redirect with success message
            return to_route('events.index')->with('alert', ['type' => 'success', 'title' => 'Berhasil mengedit kegiatan', 'message' => 'Data kegiatan pemilihan berhasil diubah']);
        } catch (\Exception $e) {
            Log::error('[EVENT] Gagal mengedit kegiatan', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('alert', ['type' => 'error', 'title' => 'Kesalahan Pengeditan Data', 'message' => 'Terjadi kesalahan saat mengedit data kegiatan. Silakan coba lagi.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function hasil()
    {
        $kegiatan = Kegiatan::where('tahun', now()->year)
            ->where('waktu_selesai', '>', now())
            ->with(['kandidat' => function ($query) {
                $query->limit(2);
            }, 'kandidat.mahasiswa.program_studi'])->get()->first();
            
        if ($kegiatan) {
            $kegiatan->toResource();
        }
        
        if (!$kegiatan) {
            $kegiatan = Kegiatan::where('tahun', now()->year)
                ->with(['kandidat' => function ($query) {
                    $query->limit(2);
                }, 'kandidat.mahasiswa.program_studi'])->latest('waktu_selesai')->first();
        }
        return Inertia::render('events/Hasil', ['kegiatan' => $kegiatan]);
    }
}
