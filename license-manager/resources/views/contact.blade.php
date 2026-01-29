<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KomikTap</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        komik: {
                            bg: '#0f0e13',
                            card: '#1e1d25',
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
        <div class="max-w-7xl mx-auto px-4 w-full flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2 hover:scale-105 transition-transform">
                <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="KomikTap Logo" class="w-10 h-10">
                <span class="text-white font-bold text-xl italic">KOMIK<span class="text-komik-primary">TAP</span></span>
            </a>
            <a href="{{ url('/') }}" class="text-gray-400 hover:text-white text-sm">Back to Home</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="flex-grow flex items-center justify-center py-20 px-4">
        <div class="max-w-4xl w-full grid md:grid-cols-2 gap-12">
            
            <!-- Contact Info -->
            <div>
                <h1 class="text-4xl font-bold text-white mb-6">Hubungi Kami</h1>
                <p class="text-komik-text mb-8 leading-relaxed">
                    Punya pertanyaan, kritik, saran, atau kendala pembayaran? 
                    Tim support kami siap membantu Anda.
                </p>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">WhatsApp</h3>
                            <p class="text-sm text-gray-500 mb-1">Fast Response (09:00 - 21:00)</p>
                            <a href="https://wa.me/6281234567890" target="_blank" class="text-komik-primary hover:text-white transition-colors font-medium">
                                +62 812-3456-7890
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Email</h3>
                            <p class="text-sm text-gray-500 mb-1">Untuk kerjasama & bisnis</p>
                            <a href="mailto:support@komiktap.id" class="text-komik-primary hover:text-white transition-colors font-medium">
                                support@komiktap.id
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                             <i class="fa-brands fa-discord"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Discord Community</h3>
                            <p class="text-sm text-gray-500 mb-1">Gabung komunitas pembaca</p>
                            <a href="#" class="text-komik-primary hover:text-white transition-colors font-medium">
                                KomikTap Official
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-[#1e1d25] p-8 rounded-2xl border border-white/5 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-6">Kirim Pesan</h3>
                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama</label>
                        <input type="text" class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Email</label>
                        <input type="email" class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors" placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Pesan</label>
                        <textarea rows="4" class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors" placeholder="Tulis pesan Anda disini..."></textarea>
                    </div>
                    <button type="button" class="w-full bg-gradient-to-r from-komik-primary to-[#ff5e00] text-white font-bold py-3 rounded-lg hover:shadow-[0_0_20px_rgba(255,121,0,0.3)] transition-all transform hover:-translate-y-1">
                        Kirim Pesan
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 text-center text-sm text-gray-600">
        &copy; {{ date('Y') }} KomikTap. All rights reserved.
    </footer>

</body>
</html>
