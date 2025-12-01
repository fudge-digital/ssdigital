<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrasi Akun Orang Tua dan Siswa</title>
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
            padding: 20px;
        }
        .header img {
            height: 80px;
            margin-bottom: 0px;
        }
        .content {
            padding: 25px;
            font-size: 14px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            border-bottom: 1px solid #e5e5e5;
            padding: 8px 5px;
            text-align: left;
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
        <div class="header">
            <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" alt="Logo Klub">
            <h3>Registrasi Berhasil</h3>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $parent->name }}</strong>,</p>
            <p>Terima kasih telah mendaftarkan anak Anda ke klub kami. Berikut adalah detail akun Anda:</p>

            <h4>Akun Orang Tua</h4>
            <ul>
                <li>Email: <strong>{{ $parent->email }}</strong></li>
                <li>Password: <strong>{{ $password }}</strong></li>
            </ul>

            <h4>Data Siswa</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Usia</th>
                        <th>Password</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student['name'] }}</td>
                            <td>{{ $student['email'] }}</td>
                            <td>{{ $student['usia'] }} tahun</td>
                            <td>{{ $student['password'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align:center;">
                <p style="margin-top:20px;">Silakan login menggunakan akun Anda di portal kami untuk melengkapi data dan memantau aktivitas siswa.</p>
                <p><a style="background:#0d5133;color:white;padding:5px 15px; border-radius:30px;text-decoration:none;" href="{{ route('login') }}">Login Disini</a></p>
                <p>Jika Anda memiliki pertanyaan, silakan hubungi admin kami melalui whatsapp</p>
                <p><a style="background:#0d5133;color:white;padding:5px 15px; border-radius:30px;text-decoration:none;text-transform:capitalize;margin-top:5px;" href="https://wa.me/62895606432020">hubungi admin</a></p>
            </div>

        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Satria Siliwangi Basketball. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
