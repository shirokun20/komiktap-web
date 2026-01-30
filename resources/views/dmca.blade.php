<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMCA - KomikTap</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        komik: {
                            bg: '#0f0e13',
                            primary: '#ff7900',
                            text: '#a0a0a0'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="w-full bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 h-20 flex items-center">
        <div class="max-w-4xl mx-auto px-4 w-full flex justify-between items-center">
             <a href="{{ url('/') }}" class="flex items-center gap-2 hover:scale-105 transition-transform">
                <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="KomikTap Logo" class="w-10 h-10">
                <span class="text-white font-bold text-xl italic">KOMIK<span class="text-komik-primary">TAP</span></span>
            </a>
            <a href="{{ url('/') }}" class="text-gray-400 hover:text-white text-sm">Back to Home</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="flex-grow py-20 px-4">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-white mb-8 border-b border-white/10 pb-4">Digital Millennium Copyright Act (DMCA)</h1>
            
            <div class="space-y-6 text-sm md:text-base leading-relaxed text-gray-300">
                <p>
                    <strong>KomikTap</strong> menghormati hak kekayaan intelektual orang lain. Kami mematuhi Undang-Undang Hak Cipta Milenium Digital (DMCA) dan hukum hak cipta lainnya yang berlaku.
                </p>

                <p>
                    Jika Anda yakin bahwa karya berhak cipta Anda telah disalin dengan cara yang melanggar hak cipta dan dapat diakses di situs web ini, harap beri tahu agen hak cipta kami sebagaimana diatur dalam Digital Millennium Copyright Act of 1998 (DMCA).
                </p>

                <h2 class="text-xl font-bold text-white mt-8 mb-4">Pengajuan Laporan Pelanggaran</h2>
                <p>
                    Agar permohonan Anda valid berdasarkan DMCA, Anda harus menyediakan informasi berikut saat memberikan pemberitahuan pelanggaran hak cipta:
                </p>
                <ul class="list-disc pl-6 space-y-2 marker:text-komik-primary">
                    <li>Tanda tangan fisik atau elektronik dari orang yang berwenang bertindak atas nama pemilik hak cipta.</li>
                    <li>Identifikasi karya berhak cipta yang diklaim telah dilanggar.</li>
                    <li>Identifikasi materi yang diklaim melanggar atau menjadi subjek aktivitas pelanggaran dan yang harus dihapus.</li>
                    <li>Informasi yang cukup memadai untuk memungkinkan penyedia layanan menghubungi pihak yang mengeluh, seperti alamat, nomor telepon, dan alamat email.</li>
                    <li>Pernyataan bahwa pihak yang mengeluh memiliki keyakinan dengan itikad baik bahwa penggunaan materi dengan cara yang dikeluhkan tidak diizinkan oleh pemilik hak cipta, agennya, atau hukum.</li>
                </ul>

                <div class="bg-red-500/10 border border-red-500/30 p-6 rounded-lg mt-8">
                    <p class="text-red-200 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Harap dicatat bahwa Anda mungkin bertanggung jawab atas kerusakan (termasuk biaya dan biaya pengacara) jika Anda secara materi memberikan pernyataan yang salah bahwa suatu materi melanggar hak cipta Anda.
                    </p>
                </div>

                <p class="mt-8">
                    Kirim laporan pelanggaran ke email: <a href="mailto:dmca@komiktap.id" class="text-komik-primary hover:underline">dmca@komiktap.id</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 text-center text-sm text-gray-600">
        &copy; {{ date('Y') }} KomikTap. All rights reserved.
    </footer>

</body>
</html>
