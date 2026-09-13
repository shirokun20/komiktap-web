<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat {{ $transaction->code }} - KomikTap</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
            <a href="{{ route('history.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-2 transition-colors">
                <i class="fas fa-arrow-left text-xs"></i> Riwayat
            </a>
        </div>
    </nav>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-2xl mx-auto">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Detail Riwayat</h1>
                <p class="text-gray-500 font-mono text-sm mt-1">{{ $transaction->code }}</p>
            </div>

            <div class="glass-card rounded-2xl p-6 space-y-5">

                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 text-sm">Status Pesanan</span>
                    @if($transaction->status === 'approved')
                        <span class="bg-green-500/15 text-green-400 text-sm px-4 py-1.5 rounded-full font-bold uppercase tracking-wide">
                            <i class="fas fa-check-circle mr-1"></i> Approved
                        </span>
                    @elseif($transaction->status === 'rejected')
                        <span class="bg-red-500/15 text-red-400 text-sm px-4 py-1.5 rounded-full font-bold uppercase tracking-wide">
                            <i class="fas fa-times-circle mr-1"></i> Rejected
                        </span>
                    @else
                        <span class="bg-yellow-500/15 text-yellow-400 text-sm px-4 py-1.5 rounded-full font-bold uppercase tracking-wide">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                    @endif
                </div>

                <hr class="border-white/5">

                <!-- Order Details -->
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Plan</span>
                        <span class="text-white font-medium">{{ $transaction->plan_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Durasi</span>
                        <span class="text-white">{{ $transaction->duration_months }} Bulan • {{ $transaction->device_quota }} Device</span>
                    </div>
                    @if($transaction->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diskon ({{ $transaction->voucher_code }})</span>
                        <span class="text-green-400">- IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Dibayar</span>
                        <span class="text-white font-bold text-base">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tanggal</span>
                        <span class="text-white">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if($transaction->payment_method)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Metode Pembayaran</span>
                        <span class="text-white">{{ $transaction->payment_method }}</span>
                    </div>
                    @endif
                </div>

                @php
                    $isDonation = $transaction->plan_name === 'Donasi' ||
                        \App\Models\DonationCampaign::where('title', $transaction->plan_name)->exists();
                    $licenseKey = $transaction->license?->key ?? null;
                @endphp

                @if(!$isDonation && $transaction->status === 'approved' && $licenseKey)
                <hr class="border-white/5">

                <!-- License Key -->
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">
                        <i class="fas fa-key text-[#ff7900] mr-1"></i> License Key
                    </p>
                    <div class="bg-[#ff7900]/5 border border-[#ff7900]/20 rounded-xl p-4 cursor-pointer hover:border-[#ff7900]/40 transition-colors"
                        onclick="copyLicense('{{ $licenseKey }}')">
                        <p class="text-white font-mono font-bold text-lg tracking-widest text-center">{{ $licenseKey }}</p>
                        <p class="text-gray-600 text-xs text-center mt-2"><i class="fas fa-copy mr-1"></i> Ketuk untuk menyalin</p>
                    </div>
                    @if($transaction->license?->expires_at)
                    <p class="text-gray-500 text-xs mt-2 text-center">
                        Berlaku hingga: <strong class="text-gray-300">{{ \Carbon\Carbon::parse($transaction->license->expires_at)->format('d M Y') }}</strong>
                    </p>
                    @endif
                </div>
                @elseif(!$isDonation && $transaction->status === 'pending')
                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-4 text-center">
                    <i class="fas fa-clock text-yellow-400 text-2xl mb-2"></i>
                    <p class="text-yellow-400 text-sm font-medium">Menunggu Konfirmasi Pembayaran</p>
                    <p class="text-gray-500 text-xs mt-1">License key akan dikirim setelah pembayaran dikonfirmasi.</p>
                </div>
                @endif

                @if($transaction->status === 'pending' && $transaction->fansku_support_id)
                <hr class="border-white/5">

                <!-- Lanjutkan Pembayaran QRIS -->
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">
                        <i class="fas fa-qrcode text-[#ff7900] mr-1"></i> Lanjutkan Pembayaran QRIS
                    </p>
                    <div class="bg-[#ff7900]/5 border border-[#ff7900]/20 rounded-xl p-5 text-center space-y-4">
                        @if($qrString)
                        <div class="flex justify-center">
                            <div class="bg-white p-3 rounded-2xl shadow-xl inline-block" id="qrBox"></div>
                        </div>
                        <p class="text-gray-500 text-xs"><i class="fas fa-qrcode mr-1"></i> Scan ulang dengan e-Wallet / m-banking untuk menyelesaikan pembayaran</p>
                        @else
                        <p class="text-yellow-400 text-sm">QR tidak tersedia di halaman ini. Buka halaman QRIS penuh untuk memuat ulang.</p>
                        @endif

                        <div class="bg-white/3 rounded-xl px-4 py-3 border border-white/6 space-y-2 text-sm text-left">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-400">Nominal Pesanan</span>
                                <span class="text-white font-semibold">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-400">Biaya Layanan QRIS</span>
                                <span class="text-white font-semibold">IDR {{ number_format($fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-white/6">
                                <span class="text-gray-400">Total Bayar</span>
                                <span class="text-[#ff7900] font-bold text-lg">IDR {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button onclick="cekUlangStatus()" id="cekUlangBtn"
                                class="flex-1 py-2.5 rounded-xl bg-[#ff7900] hover:bg-[#ff9100] text-white text-sm font-bold transition-colors">
                                <i class="fas fa-sync-alt mr-1"></i> Cek Ulang Status
                            </button>
                            <a href="/bayar/qris/{{ $transaction->code }}"
                                class="flex-1 py-2.5 rounded-xl border border-white/10 text-gray-300 hover:text-white hover:border-white/20 text-sm transition-colors">
                                <i class="fas fa-expand mr-1"></i> Halaman QRIS
                            </a>
                        </div>
                        <p class="text-gray-600 text-[11px]"><i class="fas fa-sync-alt fa-spin mr-1 text-[#ff7900]/50"></i> Status dicek otomatis tiap 10 detik…</p>
                    </div>
                </div>
                @endif

                <hr class="border-white/5">

                <!-- Actions -->
                <div class="flex gap-3">
                    <a href="{{ route('invoices.show', $transaction) }}" target="_blank"
                        class="flex-1 text-center py-2.5 rounded-xl border border-white/10 text-gray-300 hover:text-white hover:border-white/20 text-sm transition-colors">
                        <i class="fas fa-file-invoice mr-1"></i> Lihat Invoice
                    </a>
                    <a href="{{ route('history.index') }}"
                        class="flex-1 text-center py-2.5 rounded-xl bg-[#ff7900]/10 border border-[#ff7900]/20 text-[#ff7900] hover:bg-[#ff7900]/20 text-sm transition-colors">
                        <i class="fas fa-receipt mr-1"></i> Riwayat Lain
                    </a>
                </div>

            </div>
        </div>
    </main>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 left-1/2 -translate-x-1/2 translate-y-24 opacity-0 transition-all duration-300
        bg-[#1e1d25] border border-[#ff7900]/40 text-white px-6 py-3 rounded-full text-sm font-medium shadow-2xl z-50 flex items-center gap-2">
        <i class="fas fa-check-circle text-[#ff7900]"></i>
        <span id="toastMsg">Disalin!</span>
    </div>

    <footer class="border-t border-white/5 py-6 text-center text-sm text-gray-600">
        &copy; {{ date('Y') }} KomikTap. All rights reserved.
    </footer>

    <script>
        function copyLicense(key) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(key).then(() => showToast('License key disalin!'));
            } else {
                const ta = document.createElement('textarea');
                ta.value = key;
                ta.style.position = 'fixed'; ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('License key disalin!');
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMsg').textContent = msg;
            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => toast.classList.add('translate-y-24', 'opacity-0'), 3000);
        }

        @if($transaction->status === 'pending' && $transaction->fansku_support_id)
        const TX_CODE = @json($transaction->code);
        const QR_STRING = @json($qrString);

        document.addEventListener('DOMContentLoaded', () => {
            if (QR_STRING && document.getElementById('qrBox')) {
                new QRCode(document.getElementById('qrBox'), {
                    text: QR_STRING,
                    width: 220,
                    height: 220,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
            setTimeout(pollStatus, 10000);
        });

        async function fetchTxStatus() {
            const res = await fetch(`/api/checkout/fansku/${encodeURIComponent(TX_CODE)}`);
            return res.json();
        }

        function isPaid(json) {
            return json.status === 'success' && (json.data.status === 'approved' || json.data.fansku_status === 'paid');
        }

        async function pollStatus() {
            try {
                if (isPaid(await fetchTxStatus())) {
                    window.location.reload();
                    return;
                }
            } catch (e) {
                // Network hiccup — keep polling.
            }
            setTimeout(pollStatus, 10000);
        }

        async function cekUlangStatus() {
            const btn = document.getElementById('cekUlangBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengecek…';
            try {
                if (isPaid(await fetchTxStatus())) {
                    window.location.reload();
                    return;
                }
                showToast('Status masih pending, silakan scan QRIS.');
            } catch (e) {
                showToast('Gagal mengecek status, coba lagi.');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Cek Ulang Status';
        }
        @endif
    </script>
</body>
</html>
