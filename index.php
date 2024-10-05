<?php
session_start();

// Set durasi waktu session timeout (1 menit)
$session_timeout = 60;

// Cek apakah ada data session yang tersimpan dan waktu session masih berlaku
if (isset($_SESSION['result_time'])) {
    $elapsed_time = time() - $_SESSION['result_time'];
    if ($elapsed_time > $session_timeout) {
        // Hapus session jika lebih dari 1 menit
        unset($_SESSION['result'], $_SESSION['result_time'], $_SESSION['nama'], $_SESSION['member']);
    }
}

// Jika ada request GET (tombol "Hitung Lagi" diklik), hapus session dan tampilkan form
if (isset($_GET['reset'])) {
    unset($_SESSION['result'], $_SESSION['nama'], $_SESSION['result_time'], $_SESSION['member']);
}

// Jika halaman di-refresh atau diakses tanpa POST, maka tampilkan form
$form_visible = true;

// Jika form dikirim (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $total_belanja = $_POST['total_belanja'];
    $member = $_POST['member'];

    // Fungsi untuk menghitung diskon
    function hitungDiskon($total_belanja, $member)
    {
        $diskon = 0;
        $potongan_member = 0;
        $detail_potongan = ""; // untuk menyimpan informasi diskon yang diterima

        if ($member) {
            // Jika member, potongan 10% otomatis
            $potongan_member = 0.10 * $total_belanja;
            if ($total_belanja > 1000000) {
                // Diskon tambahan 15%
                $diskon = 0.15 * ($total_belanja - $potongan_member);
                $detail_potongan = "Potongan Member 10% + Diskon Tambahan 15%";
            } elseif ($total_belanja == 500000) {
                // Diskon tambahan 10%
                $diskon = 0.10 * ($total_belanja - $potongan_member);
                $detail_potongan = "Potongan Member 10% + Diskon Tambahan 10%";
            } else {
                // Hanya potongan member
                $diskon = 0;
                $detail_potongan = "Potongan Member 10%";
            }
        } else {
            // Jika bukan member
            if ($total_belanja > 1000000) {
                // Diskon 10%
                $diskon = 0.10 * $total_belanja;
                $detail_potongan = "Diskon 10%";
            } elseif ($total_belanja == 500000) {
                // Diskon 5%
                $diskon = 0.05 * $total_belanja;
                $detail_potongan = "Diskon 5%";
            } else {
                // Tidak ada diskon
                $diskon = 0;
                $detail_potongan = "Tidak ada diskon";
            }
        }

        // Total bayar setelah diskon
        $total_bayar = $total_belanja - $potongan_member - $diskon;
        return array(
            'total_belanja' => $total_belanja,
            'diskon' => $diskon,
            'potongan_member' => $potongan_member,
            'total_bayar' => $total_bayar,
            'detail_potongan' => $detail_potongan,
            'member_status' => $member ? "Member" : "Bukan Member"
        );
    }

    // Panggil fungsi untuk menghitung diskon
    $result = hitungDiskon($total_belanja, $member);

    // Simpan hasil ke session
    $_SESSION['result'] = $result;
    $_SESSION['nama'] = $nama;
    $_SESSION['member'] = $member;
    $_SESSION['result_time'] = time();

    // Redirect untuk mencegah pengulangan POST
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Jika ada hasil dalam session, sembunyikan form
if (isset($_SESSION['result'])) {
    $form_visible = false;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilham Romadhani - 23.230.0030</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/128/869/869636.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
            overflow: hidden;
        }

        canvas {
            position: absolute;
            z-index: -1;
            width: 100%;
            height: 100%;
        }

        .container {
            z-index: 1;
            width: 60%;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 30px rgba(255, 255, 255, 0.4);
            transition: transform 0.3s ease-in-out;
            text-align: center;
        }

        .container:hover {
            transform: scale(1.03);
        }

        h1 {
            text-align: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 20px;
            position: relative;
        }

        h1:before {
            content: '';
            position: absolute;
            width: 50px;
            height: 4px;
            background-color: white;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin: 10px 0 5px;
            color: white;
            font-size: 1rem;
        }

        input[type="text"],
        input[type="number"],
        select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 15px;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #74ebd5;
            outline: none;
        }

        input[type="submit"],
        .btn-reset {
            padding: 12px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.2s;
        }

        input[type="submit"]:hover,
        .btn-reset:hover {
            background-color: #5ac8d5;
        }

        .result {
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            margin-top: 20px;
            font-size: 1.1rem;
            color: white;
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.4);
            text-align: left;
        }

        .result p {
            margin: 10px 0;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .result .label {
            font-weight: bold;
            color: #74ebd5;
        }

        .btn-reset {
            background-color: red;
            padding: 12px;
            border-radius: 10px;
            color: white;
            font-size: 1.2rem;
            margin-top: 15px;
            text-align: center;
            cursor: pointer;
        }
    </style>
    <script>
        // Animasi partikel
        window.onload = function() {
            var canvas = document.createElement('canvas');
            document.body.appendChild(canvas);
            var ctx = canvas.getContext('2d');

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            var particles = [];
            for (var i = 0; i < 100; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    radius: Math.random() * 5 + 1,
                    dx: Math.random() * 2 - 1,
                    dy: Math.random() * 2 - 1
                });
            }

            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(function(p) {
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                    ctx.fill();
                    p.x += p.dx;
                    p.y += p.dy;

                    if (p.x > canvas.width || p.x < 0) p.dx *= -1;
                    if (p.y > canvas.height || p.y < 0) p.dy *= -1;
                });
                requestAnimationFrame(draw);
            }

            draw();
        };
    </script>
</head>

<body>

    <div class="container">
        <?php if ($form_visible): ?>
            <h1>Program Hitung Total Belanja</h1>
            <form method="POST">
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" required>

                <label for="total_belanja">Total Belanja (Rp):</label>
                <input type="number" id="total_belanja" name="total_belanja" required>

                <label for="member">Apakah Anda Member?</label>
                <select id="member" name="member">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>

                <input type="submit" value="Hitung Sekarang">
            </form>
        <?php else: ?>
            <div class="result">
                <p><span class="label">Nama:</span> <?= htmlspecialchars($_SESSION['nama']) ?></p>
                <p><span class="label">Status Member:</span> <?= $_SESSION['result']['member_status'] ?></p>
                <p><span class="label">Total Belanja:</span> Rp <?= number_format($_SESSION['result']['total_belanja'], 0, ',', '.') ?></p>
                <p><span class="label">Potongan Member:</span> Rp <?= number_format($_SESSION['result']['potongan_member'], 0, ',', '.') ?></p>
                <p><span class="label">Diskon:</span> Rp <?= number_format($_SESSION['result']['diskon'], 0, ',', '.') ?></p>
                <p><span class="label">Detail Potongan:</span> <?= $_SESSION['result']['detail_potongan'] ?></p>
                <p><span class="label">Total Bayar:</span> Rp <?= number_format($_SESSION['result']['total_bayar'], 0, ',', '.') ?></p>
            </div>
            <form method="GET">
                <input type="hidden" name="reset" value="1">
                <input type="submit" class="btn-reset" value="Hitung Lagi">
            </form>
        <?php endif; ?>
    </div>

</body>

</html>