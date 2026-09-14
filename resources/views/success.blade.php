<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $transaction->status === 'approved' ? 'Transaction Successful' : ($transaction->status === 'rejected' ? 'Transaction Rejected' : 'Menunggu Pembayaran') }} - KomikTap</title>
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
                            text: '#a0a0a0',
                            success: '#22c55e'
                        }
                    },
                    animation: {
                        'bounce-slow': 'bounce 3s infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; }
        .glass-card {
            background: linear-gradient(145deg, rgba(30, 29, 37, 0.9), rgba(20, 20, 25, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex p-4 sm:p-6 relative overflow-x-hidden">

    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-1/4 left-1/4 w-72 h-72 sm:w-96 sm:h-96 bg-komik-primary/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-72 h-72 sm:w-96 sm:h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="glass-card max-w-2xl w-full m-auto p-6 sm:p-10 md:p-12 rounded-3xl text-center relative z-10 border-t-4 border-komik-primary shadow-[0_0_50px_rgba(0,0,0,0.5)]">

        <!-- Icon -->
        <div class="mb-6 sm:mb-8 relative inline-block">
            @if($transaction->status === 'approved')
            <div class="absolute inset-0 bg-komik-success/20 rounded-full blur-xl animate-pulse"></div>
            <div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white text-4xl md:text-6xl shadow-xl relative z-10 animate-bounce-slow">
                <i class="fas fa-check"></i>
            </div>
            @elseif($transaction->status === 'rejected')
            <div class="absolute inset-0 bg-red-500/20 rounded-full blur-xl"></div>
            <div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white text-4xl md:text-6xl shadow-xl relative z-10">
                <i class="fas fa-times"></i>
            </div>
            @else
            <div class="absolute inset-0 bg-yellow-500/20 rounded-full blur-xl animate-pulse"></div>
            <div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-yellow-400 to-amber-600 rounded-full flex items-center justify-center text-white text-4xl md:text-6xl shadow-xl relative z-10">
                <i class="fas fa-hourglass-half"></i>
            </div>
            @endif
        </div>

        @if($transaction->status === 'approved')
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-3">Transaction Received!</h1>
        <p class="text-sm sm:text-base md:text-lg text-gray-400 mb-8 sm:mb-10 max-w-lg mx-auto">
            Thank you for your order. We are verifying your proof of payment.
        </p>
        @elseif($transaction->status === 'rejected')
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-3">Transaksi Ditolak</h1>
        <p class="text-sm sm:text-base md:text-lg text-gray-400 mb-8 sm:mb-10 max-w-lg mx-auto">
            Maaf, transaksi ini ditolak atau kedaluwarsa. Silakan buat pesanan baru.
        </p>
        @else
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-3">Menunggu Pembayaran</h1>
        <p class="text-sm sm:text-base md:text-lg text-gray-400 mb-8 sm:mb-10 max-w-lg mx-auto">
            Pesanan Anda sudah dibuat. Selesaikan pembayaran QRIS agar status berubah menjadi approved otomatis.
        </p>
        @endif

        <!-- Transaction Details -->
        <div class="bg-[#0f0e13]/50 rounded-2xl p-4 sm:p-6 md:p-8 mb-8 sm:mb-10 text-left border border-white/5 space-y-3 sm:space-y-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Transaction Code</span>
                <span class="text-white font-mono font-bold tracking-wider text-base md:text-lg min-w-0 break-all">{{ $transaction->code }}</span>
            </div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Plan</span>
                <span class="text-white font-medium text-base sm:text-lg min-w-0 break-words">{{ $transaction->plan_name }}</span>
            </div>

            @if($transaction->discount_amount > 0)
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Discount ({{ $transaction->voucher_code }})</span>
                <span class="text-green-500 font-medium min-w-0 break-words">- IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Nominal Pesanan</span>
                <span class="text-white font-semibold min-w-0 break-words">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>

            @php
                $fanskuRaw = $transaction->fansku_raw_response ?? [];
                $fanskuFee = $fanskuRaw['fee'] ?? null;
                $fanskuTotal = $fanskuRaw['total_amount'] ?? null;
            @endphp

            @if($transaction->fansku_support_id && !is_null($fanskuFee))
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Biaya Layanan QRIS (0,6%)</span>
                <span class="text-white font-semibold min-w-0 break-words">IDR {{ number_format($fanskuFee, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">{{ $transaction->status === 'approved' ? 'Total Paid' : 'Total Tagihan' }}</span>
                <span class="text-white font-bold text-base sm:text-lg min-w-0 break-words">IDR {{ number_format($fanskuTotal ?? $transaction->amount, 0, ',', '.') }}</span>
            </div>
            @if($transaction->fansku_support_id)
            <p class="text-gray-300 text-[13px] sm:text-sm leading-relaxed">
                <i class="fas fa-info-circle mr-1 text-komik-primary"></i>
                Biaya layanan diteruskan ke penyedia pembayaran (Fansku/Xendit), bukan tambahan dari KomikTap.
            </p>
            @endif

            @if($transaction->fansku_support_id)
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Fansku Reference</span>
                <span class="text-white font-mono text-sm min-w-0 break-all">{{ $transaction->fansku_code ?? $transaction->fansku_support_id }}</span>
            </div>
            @endif

            @if($transaction->tripay_reference)
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">TriPay Reference</span>
                <span class="text-white font-mono text-sm min-w-0 break-all">{{ $transaction->tripay_reference }}</span>
            </div>
            @endif

            @if($transaction->tripay_payment_method)
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Payment Method</span>
                <span class="text-white font-medium min-w-0 break-words">{{ $transaction->tripay_payment_method }}</span>
            </div>
            @endif

            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-300 shrink-0">Status</span>
                @if($transaction->status === 'approved')
                    <span class="self-start md:self-auto bg-green-500/10 text-green-500 text-xs md:text-sm px-3 py-1 rounded-lg font-bold uppercase tracking-wide">Approved</span>
                @elseif($transaction->status === 'rejected')
                    <span class="self-start md:self-auto bg-red-500/10 text-red-500 text-xs md:text-sm px-3 py-1 rounded-lg font-bold uppercase tracking-wide">Rejected</span>
                @else
                    <span class="self-start md:self-auto bg-yellow-500/10 text-yellow-500 text-xs md:text-sm px-3 py-1 rounded-lg font-bold uppercase tracking-wide">Pending</span>
                @endif
            </div>
        </div>

        <!-- Next Steps -->
        <div class="space-y-4">
            @if($transaction->status === 'approved')
            <div class="text-[13px] sm:text-sm text-gray-300 leading-relaxed">
                <i class="fas fa-info-circle mr-1 text-komik-primary"></i>
                Check your WhatsApp/Email regularly. We will send the <b>License Key</b> once approved.
            </div>
            @elseif($transaction->status === 'rejected')
            <div class="text-[13px] sm:text-sm text-gray-300 leading-relaxed">
                <i class="fas fa-info-circle mr-1 text-red-400"></i>
                Transaksi ini tidak dapat dilanjutkan. Silakan buat pesanan baru jika masih membutuhkan layanan.
            </div>
            @else
            <div class="text-[13px] sm:text-sm text-gray-300 leading-relaxed">
                <i class="fas fa-qrcode mr-1 text-komik-primary"></i>
                Status masih <b>Pending</b> — pembayaran belum diterima. Kembali ke halaman bayar untuk scan QRIS, lalu refresh halaman ini.
            </div>
            @endif

            <a href="{{ url('/') }}" class="block w-full py-4 bg-white/5 hover:bg-white/10 text-white font-medium rounded-xl transition-colors border border-white/5">
                <i class="fas fa-arrow-left mr-2"></i> Back to Home
            </a>

            @if($transaction->status === 'approved')
            <a href="{{ route('invoices.show', $transaction) }}" target="_blank" class="block w-full py-2 text-sm text-komik-primary hover:text-white transition-colors">
                View Invoice
            </a>
            @endif
        </div>

    </div>

</body>
</html>
