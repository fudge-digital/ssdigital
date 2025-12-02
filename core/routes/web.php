<?php

use App\Models\User;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Parents\ParentDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\AdminIuranController;
use App\Http\Controllers\Admin\AdminParentController;
use App\Http\Controllers\IuranRequestController;
use App\Http\Controllers\Siswas\SiswaController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Posts\CategoryController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\IuranBulananController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParentRegistrationMail;
use Illuminate\Support\Str;
use App\Helpers\StudentHelper;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard Orang Tua
    Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])
        ->middleware('role:orang_tua') // hanya orang tua
        ->name('parent.dashboard');
    
    Route::post('/parent/re-registration', [ParentDashboardController::class, 'reRegistration'])
    ->name('parent.re-registration');

    Route::post('/admin/pembayaran/re-registration-approve/{id}',
        [PembayaranController::class, 'approveReRegistration'])
        ->name('admin.re-registration.approve');

    // Dashboard Siswa
    Route::get('/siswa/dashboard', [SiswaController::class, 'dashboard'])
    ->middleware(['auth','role:siswa'])->name('siswa.dashboard');


    // Detail siswa
    Route::get('/siswa/{id}', [SiswaController::class, 'show'])->name('siswa.show');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::post('/siswa-profile/{id}/update-photo', [SiswaController::class, 'updatePhoto'])
    ->name('siswa.updatePhoto');

    // Dokumen Siswa
    Route::post('/siswa/{student}/document/{jenis}', [StudentDocumentController::class, 'store'])
    ->name('document.upload');
    Route::delete('/document/{document}', [StudentDocumentController::class, 'destroy'])
    ->name('document.delete');

    //Iuran Bulanan
    Route::get('/parent/iuran', [IuranBulananController::class, 'index'])->name('parent.iuran.index');

    // Route::post('/iuran/{iuran}/upload-bukti', [IuranBulananController::class, 'uploadBukti'])
    //     ->name('iuran.upload');

    // Request tagihan
    Route::get('/iuran/request', [IuranBulananController::class, 'requestForm'])->name('iuran.request.form');
    Route::post('/iuran/request', [IuranBulananController::class, 'submitRequest'])->name('iuran.request');

    //UploadBukti
    Route::post('/iuran/{iuran}/upload-bukti', [IuranBulananController::class, 'uploadBuktiTotal'])
        ->name('iuran.uploadTotal');

    Route::post('/iuran/{iuran}/verify', [IuranBulananController::class, 'verify'])
        ->middleware('auth', 'role:admin,super_admin') // atau role middleware
        ->name('iuran.verify');

    // Notification Route
    Route::post('/notifications/read-all', function () { 
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.readAll');

});

// Tampilkan form pendaftaran
Route::get('/pendaftaran', [RegistrationController::class, 'create'])->name('register.form');

// Proses form pendaftaran
Route::post('/pendaftaran', [RegistrationController::class, 'store'])->name('register.store');

// Halaman sukses
Route::get('/pendaftaran/sukses', function () {
    return view('auth.register_success');
})->name('register.success');

Route::post('/parent/upload-pembayaran', [ParentDashboardController::class, 'uploadPembayaran'])
    ->middleware(['auth','role:orang_tua'])
    ->name('parent.upload-pembayaran');

// Dashboard Admin & Super Admin
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('admin.pembayaran.index');
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('/pembayaran/{id}/verify', [PembayaranController::class, 'verify'])->name('admin.pembayaran.verify');
    Route::post('/pembayaran/{id}/suspend', [PembayaranController::class, 'suspend'])->name('admin.pembayaran.suspend');

    route::get('/iuran', [AdminIuranController::class, 'index'])->name('admin.iuran.index');
    Route::post('/iuran/generate', [AdminIuranController::class, 'generate'])->name('admin.iuran.generate');
    Route::get('/iuran/verifikasi', [AdminIuranController::class, 'verifikasi'])->name('admin.iuran.verifikasi');
    Route::post('/iuran/{id}/approve', [AdminIuranController::class, 'approve'])->name('admin.iuran.approve');
    Route::post('/admin/iuran/bulk-verify',[AdminIuranController::class,'bulkVerify'])
    ->name('admin.iuran.bulkVerify');
    //Bulk Month Iuran
    Route::get('/iuran/requests', [AdminIuranController::class, 'requests'])->name('admin.iuran.requests');
    Route::post('/iuran/approve/{id}', [AdminIuranController::class, 'approveRequest'])->name('admin.iuran.approve');
    Route::get('/iuran/request-detail/{id}', [AdminIuranController::class, 'requestDetail']);

    //Liat Pareng & Promo
    Route::get('/parents', [AdminParentController::class, 'index'])->name('parents.index');
    Route::post('/parents/{id}/promo', [AdminParentController::class, 'updatePromo'])->name('parents.updatePromo');

});

// Dashboard Admin Post
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::resource('posts', PostController::class)->except(['show']);
    Route::resource('categories', CategoryController::class);
});

// Semua role bisa baca
Route::get('/post/{slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/berita', [PostController::class, 'public'])->name('posts.public');



require __DIR__.'/auth.php';
