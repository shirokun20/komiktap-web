<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QRIS - {{ $transaction->code }}</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
                            text: '#a0a0a0',
                            heading: '#ffffff',
                            border: '#2a2935',
                            accent: '#FFD400'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .bg-grid {
            background-size: 40px 40px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            -webkit-mask-image: radial-gradient(circle at center, black, transparent 80%);
            mask-image: radial-gradient(circle at center, black, transparent 80%);
        }
        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="antialiased font-sans selection:bg-komik-primary selection:text-white min-h-screen">

    <div class="fixed inset-0 bg-grid opacity-20 pointer-events-none -z-10"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-[#ff7900]/8 rounded-full blur-[150px] -z-10 pointer-events-none"></div>

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-[#0f0e13]/85 backdrop-blur-xl border-b border-white/5">
        <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#ff7900]/40 to-transparent"></div>
        <div class="max-w-2xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-3 text-gray-400 hover:text-white transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-white/10 transition-colors">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </div>
                    <span class="text-sm font-medium hidden sm:block">Beranda</span>
                </a>
                <div class="flex items-center gap-2">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="Logo" class="w-7 h-7">
                    <span class="text-base font-black text-white">KOMIK<span class="text-[#ff7900]">TAP</span></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
                    <span class="text-gray-400 text-xs font-medium">Menunggu</span>
                </div>
            </div>
        </div>
    </nav>

    @php $isDonation = str_starts_with($transaction->code, 'KURON-PEDULI'); @endphp

    <!-- Main -->
    <main class="pt-20 pb-16 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

            <div class="mb-6 sm:mb-8 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff7900]/10 border border-[#ff7900]/20 text-[#ff7900] text-xs font-semibold tracking-wider mb-3">
                    <i class="fas fa-qrcode"></i>
                    <span>QRIS Otomatis</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white leading-tight">Scan QRIS untuk Membayar</h1>
                <p class="text-gray-400 text-xs sm:text-sm mt-1">Halaman ini aman di-refresh — data QR tersimpan di server.</p>
            </div>

            <div class="glass-card rounded-2xl p-5 sm:p-6">
                <div id="payPanel" class="space-y-4">
                    <div class="flex items-center gap-2 text-green-400 text-sm font-semibold">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $isDonation ? 'Donasi' : 'Pesanan' }} dibuat! Scan QRIS di bawah untuk membayar.</span>
                    </div>

                    <div class="flex items-center justify-between text-sm gap-2">
                        <span class="text-gray-500">{{ $isDonation ? 'Donasi' : 'Paket' }}</span>
                        <span class="text-white font-semibold text-right break-words min-w-0">{{ $transaction->plan_name }}</span>
                    </div>

                    <div class="bg-white/3 rounded-xl px-4 py-3 border border-white/6 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-400">Nominal {{ $isDonation ? 'Donasi' : 'Pesanan' }}</span>
                            <span class="text-white font-semibold">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-400">Biaya Layanan QRIS (0,6%)</span>
                            <span class="text-white font-semibold">IDR {{ number_format($fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-white/6">
                            <span class="text-gray-400">Total {{ $isDonation ? 'Donasi' : 'Bayar' }}</span>
                            <span class="text-[#ff7900] font-bold text-lg">IDR {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <p class="text-gray-600 text-[11px] leading-relaxed">
                        <i class="fas fa-info-circle mr-1 text-[#ff7900]/50"></i>
                        Biaya layanan diteruskan ke penyedia pembayaran (Fansku/Xendit), bukan tambahan dari KomikTap.
                    </p>

                    @if($qrString)
                    <div class="flex flex-col items-center gap-2 pt-1">
                        <p class="text-gray-500 text-xs font-semibold tracking-wider uppercase">Scan QRIS (Fansku)</p>
                        <div class="bg-white p-3 rounded-2xl shadow-xl inline-block" id="qrBox"></div>
                        <p class="text-center text-gray-600 text-xs mt-1"><i class="fas fa-qrcode mr-1"></i> Scan dengan aplikasi e-Wallet / m-banking</p>
                    </div>
                    @else
                    <p class="text-red-400 text-sm text-center">QR tidak tersedia. Hubungi admin.</p>
                    @endif

                    <div class="text-xs text-gray-500 text-center font-mono break-all">Ref: {{ $transaction->code }}</div>

                    <div class="flex items-center justify-center gap-2 text-gray-600 text-xs pt-1">
                        <i class="fas fa-sync-alt fa-spin text-[#ff7900]/50"></i>
                        <span>Memeriksa status pembayaran otomatis…</span>
                    </div>

                    <a href="/success/{{ $transaction->code }}" class="block w-full text-center text-xs text-gray-500 hover:text-gray-300 transition-colors">
                        Lihat status pesanan →
                    </a>
                </div>

                <div id="paidPanel" class="hidden text-center py-8 space-y-4">
                    <div class="w-20 h-20 rounded-full bg-green-500/15 border border-green-500/30 flex items-center justify-center mx-auto">
                        <i class="fas fa-check text-3xl text-green-400"></i>
                    </div>
                    <h2 class="text-white font-bold text-xl">Pembayaran Diterima!</h2>
                    <p class="text-gray-400 text-sm">Mengalihkan ke halaman status…</p>
                    <a href="/success/{{ $transaction->code }}" class="inline-block text-sm text-[#ff7900] hover:text-white transition-colors">
                        Lihat status pesanan →
                    </a>
                </div>
            </div>

        </div>
    </main>

    <script>
        const TX_CODE = @json($transaction->code);
        const QR_STRING = @json($qrString);
        let paid = false;

        document.addEventListener('DOMContentLoaded', () => {
            if (QR_STRING) {
                new QRCode(document.getElementById('qrBox'), {
                    text: QR_STRING,
                    width: 240,
                    height: 240,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
            setTimeout(pollStatus, 5000);
        });

        async function pollStatus() {
            if (paid) return;
            try {
                const res = await fetch(`/api/checkout/fansku/${encodeURIComponent(TX_CODE)}`);
                const json = await res.json();
                if (json.status === 'success' && (json.data.status === 'approved' || json.data.fansku_status === 'paid')) {
                    onPaid();
                    return;
                }
            } catch (e) {
                // Network hiccup — keep polling.
            }
            setTimeout(pollStatus, 5000);
        }

        function onPaid() {
            paid = true;
            document.getElementById('payPanel').classList.add('hidden');
            document.getElementById('paidPanel').classList.remove('hidden');
            setTimeout(() => { window.location.href = `/success/${encodeURIComponent(TX_CODE)}`; }, 3000);
        }
    </script>
</body>
</html>
