<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Pembayaran Pendaftaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Barlow', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 40px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #0d5133;
            color: white;
            text-align: center;
            padding: 20px 30px;
        }
        .header img {
            height: 80px;
            margin-bottom: 5px;
        }
        .content {
            padding: 25px;
            font-size: 14px;
            line-height: 1.6;
        }
        .content h4 {
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 16px;
            font-weight: 600;
            color: #0d5133;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .table th, .table td {
            border-bottom: 1px solid #e5e5e5;
            padding: 8px 5px;
            text-align: left;
            font-size: 13px;
        }
        .btn {
            background:#0d5133;
            color:white !important;
            padding:10px 20px;
            border-radius:30px;
            text-decoration:none;
            display:inline-block;
            margin-top:15px;
            font-weight:600;
        }
        .footer {
            background: #f1f1f1;
            text-align: center;
            font-size: 12px;
            padding: 15px;
            color: #555;
        }
    </style>
</head>

<body>
<div class="container">

    {{-- HEADER --}}
    <div class="header">
        <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" alt="Logo Klub">
        <h3>Pengajuan Verifikasi Pembayaran</h3>
    </div>

    {{-- CONTENT --}}
    <div class="content">
        <p>Halo Admin,</p>
        <p>Anda menerima pengajuan <strong>{{ $pembayaran->jenis === 'pendaftaran_baru' ? 'Pendaftaran Baru' : 'Registrasi Ulang' }}</strong> dari orang tua berikut:</p>

        {{-- DATA ORANG TUA --}}
        <h4>Data Orang Tua</h4>
        <ul>
            <li>Nama: <strong>{{ $pembayaran->user->name }}</strong></li>
            <li>Email: <strong>{{ $pembayaran->user->email }}</strong></li>
            @if($pembayaran->user->userProfile)
                <li>No. HP: <strong>{{ $pembayaran->user->userProfile->phone ?? '-' }}</strong></li>
            @endif
        </ul>

        {{-- DATA SISWA --}}
        <h4>Data Siswa</h4>
        <table class="table">
            <thead>
            <tr>
                <th>Nama</th>
                <th>Kelompok Umur</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($pembayaran->details as $detail)
                <tr>
                    <td>{{ $detail->siswaProfile->nama_lengkap ?? '-' }}</td>
                    <td>{{ $detail->siswaProfile->kelompok_umur ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- INFORMASI PEMBAYARAN --}}
        <h4>Informasi Pembayaran</h4>
        <ul>
            <li>Jenis: <strong>{{ $pembayaran->jenis === 'pendaftaran_baru' ? 'Pendaftaran Baru' : 'Registrasi Ulang' }}</strong></li>

            @if($pembayaran->jenis === 'pendaftaran_baru')
                <li>Total Pembayaran:
                    <strong>Rp {{ number_format($pembayaran->total_pembayaran, 0, ',', '.') }}</strong>
                </li>
                <li>Bukti pembayaran telah ditambahkan oleh orang tua.</li>
            @else
                <li>Tidak ada pembayaran (Registrasi Ulang).</li>
            @endif
        </ul>

        {{-- BUTTON --}}
        <div style="text-align:center;">
            <a href="{{ url('/admin/pembayaran') }}" class="btn">Cek Dashboard</a>
        </div>

        <p style="margin-top:25px;">
            Mohon segera lakukan pengecekan dan verifikasi agar akun siswa dapat diproses.
        </p>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        &copy; {{ date('Y') }} Satria Siliwangi Basketball. Semua hak dilindungi.
    </div>

</div>
</body>
</html>
