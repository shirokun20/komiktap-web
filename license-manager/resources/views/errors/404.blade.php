<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - KomikTap</title>
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
                            primaryHover: '#ff9100',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; }
        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff7900 0%, #ff5e00 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(255, 121, 0, 0.4); }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="/" class="flex items-center gap-2">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="Logo" class="w-10 h-10">
                    <span class="text-white font-bold text-xl italic tracking-tighter">KOMIK<span class="text-komik-primary">TAP</span></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center relative px-4 pt-20">
        <!-- Background Splatter -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-red-500/10 rounded-full blur-[100px] -z-10"></div>

        <div class="glass-card p-10 rounded-3xl max-w-lg w-full text-center relative overflow-hidden">
            <div class="mb-8">
                <div class="w-24 h-24 mx-auto bg-gray-800 rounded-2xl flex items-center justify-center mb-6 shadow-2xl relative">
                    <i class="fas fa-ghost text-5xl text-gray-600"></i>
                    <div class="absolute -top-2 -right-2 bg-red-500 text-white font-bold text-xs px-2 py-1 rounded">404</div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Page Not Found</h1>
                <p class="text-gray-400">Oops! Halaman yang kamu cari sepertinya sudah menghilang atau dipindahkan.</p>
            </div>

            <div class="flex flex-col gap-4">
                <a href="/" class="btn-primary py-4 rounded-xl text-lg font-bold flex items-center justify-center gap-3">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center text-gray-600 text-sm">
        &copy; {{ date('Y') }} KomikTap.
    </footer>

</body>
</html>
