<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrasi Ulang Diverifikasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family:Barlow, sans-serif; background:#f5f7fa; margin:0; padding:0; }
        .container { max-width:650px; background:#fff; margin:40px auto; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .header { background:#0d5133; color:#fff; text-align:center; padding:25px; }
        .header img { height:85px; }
        .content { padding:28px; font-size:15px; line-height:1.55; color:#333; }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        td,th { padding:8px 6px; border-bottom:1px solid #ececec; text-align:left; }
        th { background:#f0f0f0; }
        .button { display:inline-block; padding:10px 22px; background:#0d5133; color:#fff !important; border-radius:30px; text-decoration:none; margin-top:20px; }
        .footer { background:#f1f1f1; text-align:center; font-size:12px; padding:15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" alt="Logo">
            <h3>Registrasi Ulang Diverifikasi</h3>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $parent->userProfile->nama_lengkap ?? $parent->name }}</strong>,</p>
            <p>Registrasi ulang siswa telah diverifikasi oleh admin dan siswa siap diaktifkan.</p>
            <h4>Detail Siswa</h4>
            <table>
                <thead><tr><th>Nama Siswa</th><th>Kelompok Umur</th></tr></thead>
                <tbody>
                    @foreach($details as $detail)
                        <tr>
                            <td>{{ $detail->siswa->name }}</td>
                            <td>{{ $detail->siswa->siswaProfile->kategori_umur ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="text-align:center">
                <a href="{{ route('parent.dashboard') }}" class="button">Cek Dashboard</a>
            </div>
        </div>
        <div class="footer">&copy; {{ date('Y') }} Satria Siliwangi Basketball</div>
    </div>
</body>
</html>
