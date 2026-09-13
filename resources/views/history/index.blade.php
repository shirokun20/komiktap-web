<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian - KomikTap</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        komik: { bg: '#0f0e13', card: '#1e1d25', primary: '#ff7900', text: '#a0a0a0' }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; }
        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0,0,0,0.37);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="w-full bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 h-16 flex items-center">
        <div class="max-w-4xl mx-auto px-4 w-full flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2 hover:scale-105 transition-transform">
                <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="Logo" class="w-8 h-8">
                <span class="text-white font-bold text-lg italic">KOMIK<span class="text-[#ff7900]">TAP</span></span>
            </a>
            <div class="flex items-center gap-3">
                <span class="text-gray-400 text-xs hidden sm:block">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white text-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-sign-out-alt text-xs"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-2xl mx-auto">

            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff7900]/10 border border-[#ff7900]/20 text-[#ff7900] text-xs font-semibold tracking-wider mb-3">
                    <i class="fas fa-receipt"></i> Riwayat Pembelian
                </div>
                <h1 class="text-3xl font-bold text-white">Riwayat Pembelian</h1>
                <p class="text-gray-400 text-sm mt-2">Transaksi dengan email <strong class="text-[#ff7900]">{{ auth()->user()->email }}</strong></p>
            </div>

            @if($transactions->count() > 0)
                <div class="space-y-4">
                    @foreach($transactions as $tx)
                    <a href="{{ route('history.show', $tx->code) }}"
                        class="glass-card rounded-2xl p-5 flex items-center justify-between gap-4 hover:border-[#ff7900]/30 transition-all block group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-white font-mono font-bold text-sm">{{ $tx->code }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase
                                    {{ $tx->status === 'approved' ? 'bg-green-500/15 text-green-400' : ($tx->status === 'rejected' ? 'bg-red-500/15 text-red-400' : 'bg-yellow-500/15 text-yellow-400') }}">
                                    {{ $tx->status }}
                                </span>
                            </div>
                            <p class="text-gray-300 text-sm font-medium truncate">{{ $tx->plan_name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">
                                IDR {{ number_format($tx->amount, 0, ',', '.') }} •
                                {{ $tx->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-600 group-hover:text-[#ff7900] transition-colors flex-shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="glass-card rounded-2xl p-10 text-center">
                    <i class="fas fa-inbox text-4xl text-gray-600 mb-4"></i>
                    <h3 class="text-white font-semibold mb-2">Belum ada riwayat pembelian</h3>
                    <p class="text-gray-500 text-sm mb-2">Transaksi dengan email ini belum ditemukan. Riwayat lama berbasis nomor WA tidak tampil di sini.</p>
                    <p class="text-gray-500 text-sm mb-6">Masih bisa cek manual lewat <a href="{{ route('orders.index') }}" class="text-[#ff7900] hover:underline">Cek Pesanan</a>.</p>
                    <a href="{{ url('/#pricing') }}"
                        class="inline-block bg-gradient-to-r from-[#ff7900] to-[#ff5e00] text-white font-bold px-6 py-3 rounded-xl text-sm hover:shadow-[0_0_20px_rgba(255,121,0,0.3)] transition-all">
                        Beli Premium
                    </a>
                </div>
            @endif

        </div>
    </main>

    <footer class="border-t border-white/5 py-6 text-center text-sm text-gray-600">
        &copy; {{ date('Y') }} KomikTap. All rights reserved.
    </footer>

</body>
</html>
