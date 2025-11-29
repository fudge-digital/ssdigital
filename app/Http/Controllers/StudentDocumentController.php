<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use App\Models\SiswaProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudentDocumentController extends Controller
{
    use AuthorizesRequests;

    public function index($siswaProfileId)
    {
        $siswa = SiswaProfile::with('documents')->findOrFail($siswaProfileId);
        return view('siswa.documents.index', compact('siswa'));
    }

    public function store(Request $request, SiswaProfile $student, $jenis)
    {
        $this->authorize('create', StudentDocument::class);

        $request->validate([
            "file"  => "required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp",
            "title" => "nullable|string|max:255|required_if:jenis,lain",
        ]);

        $file       = $request->file("file");
        $original   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext        = $file->getClientOriginalExtension();
        $filename   = Str::slug($original) . "_" . time() . "." . $ext;

        // --- Folder spesifik siswa ---
        $folderName = "dokumen_siswa/{$student->id}";

        // Setting environment seperti upload bukti pembayaran
        $useDirectPublicStorage = env('USE_DIRECT_PUBLIC_STORAGE', false);

        if (!$useDirectPublicStorage && file_exists(public_path('storage'))) {

            // 🖥 LOCAL: pakai disk 'public'
            $storedPath = $file->storeAs($folderName, $filename, 'public'); 
            // hasil: dokumen_siswa/{id}/file.ext

        } else {

            // 🌐 SERVER: simpan langsung ke public_html/storage/{folder}
            $destinationPath = public_path("storage/{$folderName}");

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $storedPath = "storage/{$folderName}/{$filename}";
        }

        StudentDocument::create([
            "siswa_profile_id" => $student->id,
            "type"             => $jenis,
            "title"            => $request->title ?? null,
            "file_path"        => $storedPath,
            "uploaded_by"      => $request->user()->id,
        ]);

        return back()->with("success", "Dokumen berhasil diupload.");
    }

    public function destroy(StudentDocument $document)
    {
        // Hanya admin & orang_tua yang boleh hapus
        if (!auth()->user()->hasRole(['admin', 'orang_tua'])) {
            abort(403);
        }

        // Hapus file fisik jika ada
        if ($document->file_path && file_exists(public_path('storage/' . $document->file_path))) {
            unlink(public_path('storage/' . $document->file_path));
        }

        // Hapus dari database
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    // Optional: preview/download (stream) — disarankan untuk protected preview
    public function preview($id)
    {
        $doc = StudentDocument::findOrFail($id);
        $this->authorize('view', $doc);

        $path = storage_path('app/public/' . $doc->file_path);
        if (!file_exists($path)) abort(404);

        $mime = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mime
        ]);
    }
}
