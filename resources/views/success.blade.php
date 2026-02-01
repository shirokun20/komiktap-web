<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Successful - KomikTap</title>
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
<body class="antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-komik-primary/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="glass-card max-w-2xl w-full p-10 md:p-12 rounded-3xl text-center relative z-10 border-t-4 border-komik-primary shadow-[0_0_50px_rgba(0,0,0,0.5)]">
        
        <!-- Icon -->
        <div class="mb-8 relative inline-block">
            <div class="absolute inset-0 bg-komik-success/20 rounded-full blur-xl animate-pulse"></div>
            <div class="w-24 h-24 md:w-32 md:h-32 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white text-4xl md:text-6xl shadow-xl relative z-10 animate-bounce-slow">
                <i class="fas fa-check"></i>
            </div>
        </div>

        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Transaction Received!</h1>
        <p class="text-base md:text-lg text-gray-400 mb-10 max-w-lg mx-auto">
            Thank you for your order. We are verifying your proof of payment.
        </p>

        <!-- Transaction Details -->
        <div class="bg-[#0f0e13]/50 rounded-2xl p-6 md:p-8 mb-10 text-left border border-white/5 space-y-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center text-sm md:text-base gap-1">
                <span class="text-gray-500">Transaction Code</span>
                <span class="text-white font-mono font-bold tracking-wider text-base md:text-lg text-wrap break-all">{{ $transaction->code }}</span>
            </div>
            <div class="flex justify-between items-center text-sm md:text-base">
                <span class="text-gray-500">Plan</span>
                <span class="text-white font-medium text-lg">{{ $transaction->plan_name }}</span>
            </div>
            
            @if($transaction->discount_amount > 0)
            <div class="flex justify-between items-center text-sm md:text-base">
                <span class="text-gray-500">Discount ({{ $transaction->voucher_code }})</span>
                <span class="text-green-500 font-medium">- IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="flex justify-between items-center text-sm md:text-base">
                <span class="text-gray-500">Total Paid</span>
                <span class="text-white font-bold text-lg">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>

             <div class="flex justify-between items-center text-sm md:text-base">
                <span class="text-gray-500">Status</span>
                <span class="bg-yellow-500/10 text-yellow-500 text-xs md:text-sm px-3 py-1 rounded-lg font-bold uppercase tracking-wide">Pending</span>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="space-y-4">
             <div class="text-xs text-gray-500 leading-relaxed">
                <i class="fas fa-info-circle mr-1 text-komik-primary"></i>
                Check your WhatsApp/Email regularly. We will send the <b>License Key</b> once approved.
            </div>

            <a href="{{ url('/') }}" class="block w-full py-4 bg-white/5 hover:bg-white/10 text-white font-medium rounded-xl transition-colors border border-white/5">
                <i class="fas fa-arrow-left mr-2"></i> Back to Home
            </a>
            
             <a href="{{ route('invoices.show', $transaction) }}" target="_blank" class="block w-full py-2 text-sm text-komik-primary hover:text-white transition-colors">
                View Invoice
            </a>
        </div>

    </div>

</body>
</html>
