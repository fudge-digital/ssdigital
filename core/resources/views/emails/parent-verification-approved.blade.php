<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Diverifikasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Barlow', sans-serif; background: #f5f7fa; color: #333; margin:0; padding:0; }
        .container { max-width:650px; background:#fff; margin:40px auto; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
        .header { background:#0d5133; color:#fff; text-align:center; padding:25px; }
        .header img { height:85px; }
        .content { padding:28px; font-size:15px; line-height:1.55; }
        .table { width:100%; margin-top:15px; border-collapse:collapse; }
        .table th { text-align:left; padding:8px 6px; background:#f0f0f0; }
        .table td { padding:8px 6px; border-bottom:1px solid #ececec; }
        .button { display:inline-block; background:#0d5133; padding:10px 22px; color:white !important; margin-top:18px; border-radius:30px; text-decoration:none; font-weight:bold; }
        .footer { background:#f1f1f1; text-align:center; font-size:12px; padding:15px; color:#555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" alt="Logo Klub">
            <h3 style="margin-top:10px;">Pembayaran Terverifikasi</h3>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $parent->userProfile->nama_lengkap ?? $parent->name }}</strong>,</p>
            <p>Admin telah berhasil memverifikasi pembayaran pendaftaran/iuran Anda.</p>
            <p>Jenis Pembayaran: <strong>{{ $pembayaran->jenis === 'Daftar_Ulang' ? 'Registrasi Ulang' : 'Pendaftaran Baru' }}</strong></p>
            <h4>Detail Siswa</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Kelompok Umur</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($details as $detail)
                    <tr>
                        <td>{{ $detail->siswa->name }}</td>
                        <td>{{ $detail->siswa->siswaProfile->kategori_umur ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if($pembayaran->jenis === 'Pendaftaran_Baru')
                <p style="margin-top:20px;">Total Pembayaran: <strong>Rp {{ number_format($pembayaran->jumlah_total, 0, ',', '.') }}</strong></p>
            @endif
            <div style="text-align:center;">
                <a href="{{ route('parent.iuran.index') }}" class="button">Cek Dashboard</a>
            </div>
        </div>
        <div class="footer">&copy; {{ date('Y') }} Satria Siliwangi Basketball. Semua hak dilindungi.</div>
    </div>
</body>
</html>
