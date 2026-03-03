@inject('contact', 'App\Settings\ContactSettings')
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - {{ $campaign->title }}</title>
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
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0f0e13;
            color: #b8b8b8;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black, transparent 80%);
        }

        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .text-gradient {
            background: linear-gradient(135deg, #ff7900 0%, #FFD400 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff7900 0%, #ff5e00 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            box-shadow: 0 6px 32px rgba(255, 121, 0, 0.45);
            transform: translateY(-1px);
        }

        .btn-primary:active { transform: scale(0.98); }

        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            100% { background-position: 0 0; }
        }
    </style>
</head>

<body class="antialiased font-sans selection:bg-komik-primary selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 transition-all duration-300 group hover:bg-[#0f0e13]/95">
        <div class="absolute bottom-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-komik-primary/50 to-transparent opacity-50 group-hover:opacity-100 transition-opacity"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 group/logo hover:scale-105 transition-transform duration-300">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png"
                        alt="KomikTap Logo" class="w-10 h-10 md:w-12 md:h-12 drop-shadow-[0_0_8px_rgba(255,121,0,0.5)]">
                    <div class="flex flex-col">
                        <span class="text-xl md:text-2xl font-black text-white leading-none tracking-tight">KOMIK<span class="text-komik-primary">TAP</span></span>
                        <span class="text-[0.6rem] md:text-[0.65rem] text-gray-400 font-medium tracking-widest uppercase">Peduli Sesama</span>
                    </div>
                </a>

                <!-- Back to List -->
                <a href="{{ route('donation.index') }}"
                    class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-32 pb-20 relative overflow-hidden min-h-screen">
        <!-- Background Glow -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-[500px] bg-komik-primary/10 rounded-full blur-[120px] -z-10"></div>
        <div class="fixed inset-0 bg-grid -z-20 opacity-30 pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Campaign Details -->
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-3xl overflow-hidden mb-8">
                        @if($campaign->image_path)
                        <div class="w-full h-64 md:h-80 bg-gray-800 relative">
                            <img src="{{ Storage::url($campaign->image_path) }}" alt="{{ $campaign->title }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#1e1d25] to-transparent opacity-80"></div>
                        </div>
                        @endif

                        <div class="p-8">
                            <h1 class="text-3xl font-bold text-white mb-6">{{ $campaign->title }}</h1>

                            <!-- Progress Bar (Mobile) -->
                            <div class="lg:hidden mb-8">
                                <div class="flex justify-between items-end mb-2">
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Terkumpul</p>
                                        <h3 class="text-xl font-bold text-white">IDR {{ number_format($collected, 0, ',', '.') }}</h3>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400 mb-1">Target</p>
                                        <h3 class="text-lg font-bold text-gray-300">IDR {{ number_format($campaign->target_amount, 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-700/50 rounded-full h-3 overflow-hidden relative">
                                    <div class="bg-gradient-to-r from-komik-primary to-komik-accent h-full rounded-full progress-bar-striped relative"
                                        style="width: {{ $progress }}%"></div>
                                </div>
                            </div>

                            <div class="prose prose-invert prose-sm max-w-none text-gray-300">
                                {!! Str::markdown($campaign->description ?? '') !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Donation Form -->
                <div class="lg:col-span-1">
                    <div class="glass-card rounded-3xl p-6 sticky top-28">
                        <!-- Progress Bar (Desktop) -->
                        <div class="hidden lg:block mb-8">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Terkumpul</p>
                                    <h3 class="text-xl font-bold text-white">IDR {{ number_format($collected, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700/50 rounded-full h-3 overflow-hidden relative mb-2">
                                <div class="bg-gradient-to-r from-komik-primary to-komik-accent h-full rounded-full progress-bar-striped relative"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="text-right text-xs text-gray-400">
                                dari target <span class="text-white font-bold">IDR {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-white mb-6">Nominal Donasi</h3>

                        <!-- Predefined Amounts -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <button onclick="setAmount(10000)"
                                class="amount-btn py-3 px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-komik-primary/10 hover:border-komik-primary text-gray-300 hover:text-white transition-all font-medium text-sm"
                                data-amount="10000">10k</button>
                            <button onclick="setAmount(25000)"
                                class="amount-btn py-3 px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-komik-primary/10 hover:border-komik-primary text-gray-300 hover:text-white transition-all font-medium text-sm"
                                data-amount="25000">25k</button>
                            <button onclick="setAmount(50000)"
                                class="amount-btn py-3 px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-komik-primary/10 hover:border-komik-primary text-gray-300 hover:text-white transition-all font-medium text-sm"
                                data-amount="50000">50k</button>
                            <button onclick="setAmount(100000)"
                                class="amount-btn py-3 px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-komik-primary/10 hover:border-komik-primary text-gray-300 hover:text-white transition-all font-medium text-sm"
                                data-amount="100000">100k</button>
                        </div>

                        <!-- Custom Amount -->
                        <div class="mb-6">
                            <label class="block text-gray-400 text-xs mb-2">Nominal Lainnya (Min. Rp 1.000)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">IDR</span>
                                <input type="number" id="customAmount" min="1000"
                                    class="w-full bg-black/20 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white font-bold text-lg focus:outline-none focus:border-komik-primary/50 transition-colors"
                                    placeholder="0">
                            </div>
                        </div>

                        <button onclick="processDonation()" id="lanjutBtn"
                            class="w-full py-4 rounded-xl btn-primary text-white font-bold text-lg shadow-lg shadow-komik-primary/25 transition-all flex items-center justify-center gap-2">
                            <span>Lanjut Pembayaran</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let SELECTED_AMOUNT = 0;

        function setAmount(amount) {
            document.getElementById('customAmount').value = amount;
            document.querySelectorAll('.amount-btn').forEach(btn => {
                const isActive = parseInt(btn.dataset.amount) === amount;
                btn.classList.toggle('border-komik-primary', isActive);
                btn.classList.toggle('bg-komik-primary/10', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('border-white/10', !isActive);
                btn.classList.toggle('bg-white/5', !isActive);
                btn.classList.toggle('text-gray-300', !isActive);
            });
        }

        document.getElementById('customAmount').addEventListener('input', () => {
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.classList.remove('border-komik-primary', 'bg-komik-primary/10', 'text-white');
                btn.classList.add('border-white/10', 'bg-white/5', 'text-gray-300');
            });
        });

        function processDonation() {
            const amount = parseInt(document.getElementById('customAmount').value);
            if (!amount || amount < 1000) {
                alert('Minimal donasi Rp 1.000');
                return;
            }
            SELECTED_AMOUNT = amount;

            const btn = document.getElementById('lanjutBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            window.location.href = '{{ route("donation.payment", $campaign->slug) }}?amount=' + amount;
        }
    </script>
</body>

</html>
