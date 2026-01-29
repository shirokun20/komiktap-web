<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KomikTap Premium - Unlock the Full Experience</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        komik: {
                            bg: '#0f0e13', // Darker cleaner background
                            card: '#1e1d25',
                            primary: '#ff7900', // Orange
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

        /* Modern Grid Background */
        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black, transparent 80%);
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .glass-card:hover {
            border-color: rgba(255, 121, 0, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px 0 rgba(0, 0, 0, 0.5);
        }

        /* Text Gradient */
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

        .btn-primary:active {
            transform: scale(0.98);
        }

        /* Highlight Blur */
        .glow-blob {
            position: absolute;
            background: #ff7900;
            filter: blur(80px);
            opacity: 0.15;
            z-index: 0;
            border-radius: 50%;
        }

        /* Description Markdown Styling */
        .plan-description ul {
            list-style: none;
            padding: 0;
        }
        .plan-description li {
            position: relative;
            padding-left: 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        .plan-description li::before {
            content: '\f00c';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: #22c55e; /* green-500 */
            font-size: 14px;
        }
        .plan-description strong {
            color: white;
            font-weight: 600;
        }
    </style>
</head>

<body class="antialiased">

    <!-- Navbar (Premium Cool Version) -->
    <nav
        class="fixed w-full z-50 bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 transition-all duration-300 group hover:bg-[#0f0e13]/95">
        <!-- Gradient Line Bottom -->
        <div
            class="absolute bottom-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-komik-primary/50 to-transparent opacity-50 group-hover:opacity-100 transition-opacity">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-2 group/logo hover:scale-105 transition-transform duration-300">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="KomikTap Logo" class="w-10 h-10 md:w-12 md:h-12 drop-shadow-[0_0_8px_rgba(255,121,0,0.5)]">
                    <span class="text-white font-bold text-xl md:text-2xl italic tracking-tighter">KOMIK<span class="text-komik-primary">TAP</span></span>
                </a>

                <!-- Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#"
                        class="text-gray-300 hover:text-white font-medium text-sm tracking-wide relative group/link py-2">
                        Home
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-komik-primary group-hover/link:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#"
                        class="text-gray-300 hover:text-white font-medium text-sm tracking-wide relative group/link py-2">
                        Daftar Komik
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-komik-primary group-hover/link:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#"
                        class="text-gray-300 hover:text-white font-medium text-sm tracking-wide relative group/link py-2">
                        Cara Baca
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-komik-primary group-hover/link:w-full transition-all duration-300"></span>
                    </a>
                </div>

                <!-- CTA -->
                <div class="flex items-center gap-4">
                    <a href="#"
                        class="hidden md:flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="#"
                        class="btn-primary px-6 py-2 rounded-lg text-sm font-bold shadow-lg shadow-komik-primary/20 hover:shadow-komik-primary/40 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                        <i class="fab fa-android text-lg"></i> Download App
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden hero-glow">
        <div class="max-w-4xl mx-auto px-4 text-center">

            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-komik-primary/10 border border-komik-primary/20 text-komik-primary text-xs font-semibold uppercase tracking-wider mb-6">
                <i class="fa-solid fa-crown"></i> Premium Access
            </div>

            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                Baca Komik Tanpa <br>
                <span class="text-komik-primary">Batas & Iklan</span>
            </h1>

            <p class="text-lg text-komik-text max-w-2xl mx-auto mb-10">
                Nikmati pengalaman baca manhwa, manhua, dan manga favoritmu dengan fitur premium.
                Sinkronisasi history bacaan hingga 3 device sekaligus.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#pricing"
                    class="btn-primary px-8 py-3 rounded-md font-bold text-lg shadow-lg shadow-komik-primary/20">
                    Beli Premium
                </a>
                <a href="#"
                    class="px-8 py-3 rounded-md bg-[#333] hover:bg-[#444] text-white font-medium transition-all">
                    Fitur Lengkap
                </a>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-10 relative z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div
                    class="glass-card p-8 text-center hover:-translate-y-2 transition-transform duration-300 rounded-2xl group">
                    <div
                        class="w-16 h-16 mx-auto bg-komik-card rounded-full flex items-center justify-center text-3xl text-red-500 mb-6 group-hover:bg-red-500/10 transition-colors">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">No Ads</h3>
                    <p class="text-sm text-gray-400">Hilangkan semua iklan popup dan banner yang mengganggu saat
                        membaca.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="glass-card p-8 text-center hover:-translate-y-2 transition-transform duration-300 rounded-2xl border-b-2 border-komik-primary group">
                    <div
                        class="w-16 h-16 mx-auto bg-komik-primary/10 rounded-full flex items-center justify-center text-3xl text-komik-primary mb-6 group-hover:bg-komik-primary/20 transition-colors">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Multi-Device</h3>
                    <p class="text-sm text-gray-400">Satu akun premium bisa aktif di <strong>3 Device</strong> berbeda
                        sekaligus.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="glass-card p-8 text-center hover:-translate-y-2 transition-transform duration-300 rounded-2xl group">
                    <div
                        class="w-16 h-16 mx-auto bg-komik-card rounded-full flex items-center justify-center text-3xl text-komik-yellow mb-6 group-hover:bg-komik-yellow/10 transition-colors">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Offline & PDF</h3>
                    <p class="text-sm text-gray-400">Download chapter favoritmu atau simpan sebagai PDF untuk dibaca
                        tanpa internet.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">Pilih Paket</h2>
                <p class="text-komik-text text-lg">Harga simple, tanpa biaya tersembunyi.</p>
            </div>

            <!-- Pricing Options Grid -->
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto items-center">

                <div id="plansContainer" class="contents">
                   <!-- Loading Skeleton -->
                   <div class="col-span-3 text-center py-12">
                       <i class="fas fa-circle-notch fa-spin text-komik-primary text-4xl"></i>
                       <p class="text-gray-500 mt-4">Loading plans...</p>
                   </div>
                </div>
            </div>
        </div>

        <!-- Custom Plan (Ketengan) -->
        <div class="max-w-4xl mx-auto mt-20">
            <!-- Custom Plan (Ketengan) Section -->
            <div class="glass-card p-10 rounded-3xl relative overflow-hidden mb-20">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-komik-primary/10 rounded-full blur-[60px]"></div>

                <div class="grid md:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-800/50 border border-gray-700 text-xs font-semibold text-gray-300 mb-4">
                            <i class="fas fa-sliders-h text-komik-primary"></i>
                            <span>Custom Configuration</span>
                        </div>
                        <h2 class="text-3xl font-bold text-white mb-4">Paket Ketengan</h2>
                        <p class="text-gray-400 mb-8 leading-relaxed">
                            Atur sendiri kebutuhan perangkat dan durasi langgananmu. Lebih fleksibel, bayar sesuai yang
                            kamu butuhkan.
                        </p>

                        <!-- Sliders & Inputs -->
                        <div class="space-y-8">
                            <!-- Device Count -->
                            <div>
                                <div class="flex justify-between text-sm mb-3">
                                    <span class="text-gray-300 font-medium">Jumlah Device</span>
                                    <span class="text-komik-primary font-bold"><span id="deviceCountDisplay">3</span>
                                        Device</span>
                                </div>
                                <input type="range" id="deviceSlider" min="1" max="10" value="3"
                                    class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-komik-primary hover:accent-komik-primaryHover transition-all">
                                <div class="flex justify-between text-[10px] text-gray-600 mt-2 font-mono">
                                    <span>1</span>
                                    <span>5</span>
                                    <span>10</span>
                                </div>
                            </div>

                            <!-- Duration -->
                            <div>
                                <span class="block text-sm text-gray-300 font-medium mb-3">Durasi Langganan</span>
                                <div class="grid grid-cols-4 gap-2" id="durationSelector">
                                    <button
                                        class="py-2 px-3 rounded-lg border border-gray-700 text-sm font-medium hover:border-komik-primary hover:text-white transition-all duration-btn active"
                                        data-value="1">1 Bln</button>
                                    <button
                                        class="py-2 px-3 rounded-lg border border-gray-700 text-sm font-medium hover:border-komik-primary hover:text-white transition-all duration-btn"
                                        data-value="3">3 Bln</button>
                                    <button
                                        class="py-2 px-3 rounded-lg border border-gray-700 text-sm font-medium hover:border-komik-primary hover:text-white transition-all duration-btn"
                                        data-value="6">6 Bln</button>
                                    <button
                                        class="py-2 px-3 rounded-lg border border-gray-700 text-sm font-medium hover:border-komik-primary hover:text-white transition-all duration-btn"
                                        data-value="12">1 Thn</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div
                        class="relative bg-gray-900/40 rounded-2xl p-8 border border-gray-800 flex flex-col items-center text-center">
                        <div class="mb-2 text-sm text-gray-500 font-medium">ESTIMASI BIAYA</div>
                        <div class="text-5xl font-bold text-white mb-2 tracking-tight" id="totalPrice">IDR 45K</div>
                        <div class="text-sm text-green-400 mb-8 bg-green-500/10 px-3 py-1 rounded-full border border-green-500/20"
                            id="discountBadges" style="display: none;">
                            Hemat 5%
                        </div>

                        <div class="w-full space-y-3 mb-8 text-sm text-left">
                            <div class="flex justify-between text-gray-400">
                                <span>Base Price</span>
                                <span class="text-white" id="basePriceDisplay">IDR 15,000</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Device Multiplier</span>
                                <span class="text-white">x<span id="deviceCountSummary">3</span></span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Duration</span>
                                <span class="text-white">x<span id="durationSummary">1 Bulan</span></span>
                            </div>
                            <div class="h-px bg-gray-700 my-2"></div>
                            <div class="flex justify-between text-komik-primary font-medium">
                                <span>Total Discount</span>
                                <span id="discountAmount">- IDR 0</span>
                            </div>
                        </div>

                        <button onclick="openQrisModal('Ketengan', 0, 0, 0, 'custom')"
                            class="w-full py-4 rounded-xl btn-primary font-bold text-lg shadow-lg hover:shadow-komik-primary/40 transition-all flex items-center justify-center gap-2">
                            <span>Checkout Paket Ketengan</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- FAQ -->
    <section class="pb-16 pt-1 relative z-10">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-8 text-center">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <div class="glass-card p-6 rounded-2xl hover:bg-white/5 transition-colors">
                    <h4 class="font-bold text-white text-base mb-2 flex items-center gap-3">
                        <i class="fa-regular fa-circle-question text-komik-primary text-xl"></i>
                        Bagaimana cara aktivasi?
                    </h4>
                    <p class="text-gray-400 text-sm leading-relaxed pl-8">Setelah pembayaran berhasil, Anda akan
                        mendapatkan <strong>Kode Lisensi Premium</strong> via email/WhatsApp. Masukkan kode tersebut di
                        menu <em>Settings > Activate Premium</em> pada aplikasi KomikTap.</p>
                </div>
                <div class="glass-card p-6 rounded-2xl hover:bg-white/5 transition-colors">
                    <h4 class="font-bold text-white text-base mb-2 flex items-center gap-3">
                        <i class="fa-regular fa-circle-question text-komik-primary text-xl"></i>
                        Ganti HP bagaimana?
                    </h4>
                    <p class="text-gray-400 text-sm leading-relaxed pl-8">Lisensi Anda mendukung multi-device (sesuai
                        paket). Jika Anda berganti HP, Anda bisa mereset device lama melalui Admin Panel kami atau
                        hubungi support jika mengalami kendala.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Premium Cool Version) -->
    <footer class="relative z-20 border-t border-white/5 bg-[#0f0e13]/90 backdrop-blur-xl mt-20">
        <!-- Glowing Top Border -->
        <div
            class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-komik-primary/50 to-transparent box-shadow-[0_0_20px_rgba(255,121,0,0.5)]">
        </div>

        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">

                <!-- Brand & Copyright -->
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                        <span class="text-xl font-bold italic tracking-tighter text-white">KOMIK<span
                                class="text-komik-primary">TAP</span></span>
                        <span
                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-gray-400 border border-white/5">PREMIUM</span>
                    </div>
                    <p class="text-gray-500 text-xs text-center md:text-left">
                        &copy; 2026 KomikTap. Crafted for readers.
                    </p>
                </div>

                <!-- Simple Links -->
                <div class="flex flex-wrap justify-center gap-6 md:gap-8 text-sm font-medium text-gray-400">
                    <a href="web-donasin.html"
                        class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">Donasi</a>
                    <a href="{{ route('contact') }}"
                        class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">Contact</a>
                    <a href="{{ route('dmca') }}" class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">DMCA</a>
                </div>

                <!-- Social Icons -->
                <div class="flex gap-4">
                    <a href="#"
                        class="group w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-komik-primary hover:text-black hover:scale-110 hover:shadow-[0_0_15px_rgba(255,121,0,0.4)] transition-all duration-300">
                        <i class="fab fa-facebook-f group-hover:animate-pulse"></i>
                    </a>
                    <a href="#"
                        class="group w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-komik-primary hover:text-black hover:scale-110 hover:shadow-[0_0_15px_rgba(255,121,0,0.4)] transition-all duration-300">
                        <i class="fab fa-twitter group-hover:animate-pulse"></i>
                    </a>
                    <a href="#"
                        class="group w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:bg-komik-primary hover:text-black hover:scale-110 hover:shadow-[0_0_15px_rgba(255,121,0,0.4)] transition-all duration-300">
                        <i class="fab fa-instagram group-hover:animate-pulse"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Price Calculator Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slider = document.getElementById('deviceSlider');
            const countDisplay = document.getElementById('deviceCountDisplay');
            const summaryCount = document.getElementById('deviceCountSummary');

            const durationBtns = document.querySelectorAll('.duration-btn');
            const durationSummary = document.getElementById('durationSummary');

            const priceDisplay = document.getElementById('totalPrice');
            const basePriceDisplay = document.getElementById('basePriceDisplay');
            const discountBadge = document.getElementById('discountBadges');
            const discountAmountDisplay = document.getElementById('discountAmount');

            let currentDuration = 1;
            
            // Default Values (Fallback)
            let CONFIG = {
                ketengan_base_price: 15000,
                discount_3_months: 0.05,
                discount_6_months: 0.10,
                discount_12_months: 0.20,
                device_discount_percentage: 0.02
            };

            const formatCurrency = (num) => {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(num);
            };

            function updatePrice() {
                const devices = parseInt(slider.value);
                const BASE_PRICE = CONFIG.ketengan_base_price;

                // Update UI Text
                countDisplay.textContent = devices;
                if (summaryCount) summaryCount.textContent = devices;
                if (durationSummary) durationSummary.textContent = currentDuration + (currentDuration > 1 ? ' Bulan' : ' Bulan');

                // Logic Harga
                let pricePerDevice = BASE_PRICE;
                if (devices > 1) {
                    pricePerDevice = BASE_PRICE * (1 - (devices * CONFIG.device_discount_percentage));
                }

                let baseTotal = pricePerDevice * devices * currentDuration;

                // Duration Discount
                let discountPercent = 0;
                if (currentDuration >= 12) discountPercent = CONFIG.discount_12_months;
                else if (currentDuration >= 6) discountPercent = CONFIG.discount_6_months;
                else if (currentDuration >= 3) discountPercent = CONFIG.discount_3_months;

                const discountValue = baseTotal * discountPercent;
                const finalPrice = baseTotal - discountValue;

                // Update UI Numbers
                priceDisplay.textContent = formatCurrency(finalPrice);
                if (basePriceDisplay) basePriceDisplay.textContent = formatCurrency(BASE_PRICE * devices * currentDuration);

                if (discountPercent > 0) {
                    discountBadge.style.display = 'inline-block';
                    discountBadge.textContent = `Hemat ${(discountPercent * 100).toFixed(0)}%`;
                    discountAmountDisplay.textContent = `- ${formatCurrency(discountValue)}`;
                } else {
                    discountBadge.style.display = 'none';
                    discountAmountDisplay.textContent = '- IDR 0';
                }
            }

            // Fetch Config from API
            async function fetchConfig() {
                try {
                    const response = await fetch('/api/config');
                    const json = await response.json();
                    
                    if (json.status === 'success') {
                        const data = json.data;
                        // Update Config
                        CONFIG = {
                            ketengan_base_price: parseFloat(data.ketengan_base_price),
                            discount_3_months: parseFloat(data.discount_3_months),
                            discount_6_months: parseFloat(data.discount_6_months),
                            discount_12_months: parseFloat(data.discount_12_months),
                            device_discount_percentage: parseFloat(data.device_discount_percentage)
                        };
                        updatePrice(); // Recalculate with new values
                    }
                } catch (error) {
                    console.error('Failed to fetch config, using defaults', error);
                }
            }

            // Event Listeners
            if (slider) slider.addEventListener('input', updatePrice);

            durationBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    durationBtns.forEach(b => {
                        b.classList.remove('active', 'border-komik-primary', 'text-white', 'bg-komik-primary', 'shadow-lg');
                        b.classList.add('border-gray-700', 'text-gray-400');
                    });

                    const target = e.target.closest('button'); // handle click on inner span if any
                    target.classList.remove('border-gray-700', 'text-gray-400');
                    target.classList.add('active', 'border-komik-primary', 'text-white', 'bg-komik-primary', 'shadow-lg');

                    currentDuration = parseInt(target.dataset.value);
                    updatePrice();
                });
            });

            // Fetch Plans from API
            async function fetchPlans() {
                try {
                    const response = await fetch('/api/plans');
                    const json = await response.json();
                    
                    if (json.status === 'success') {
                        renderPlans(json.data);
                    }
                } catch (error) {
                    console.error('Failed to fetch plans', error);
                }
            }

            function renderPlans(plans) {
                const container = document.getElementById('plansContainer');
                container.innerHTML = ''; // Clear loading

                const formatK = (price) => Math.round(price / 1000) + 'K';
                
                // Helper to convert markdown-like bullets to HTML
                const parseDesc = (desc) => {
                     // Very simple parser for bullet points
                     if (!desc) return '';
                     const lines = desc.split('\n');
                     let html = '<ul>';
                     lines.forEach(line => {
                         if (line.trim().startsWith('-')) {
                             html += `<li>${line.replace('-', '').trim()}</li>`;
                         } else {
                             html += line + '<br>';
                         }
                     });
                     html += '</ul>';
                     return html;
                };

                plans.forEach(plan => {
                    let html = '';
                    if (plan.is_recommended) {
                        html = `
                        <div class="glass-card p-8 rounded-3xl relative transform md:-translate-y-4 border-komik-primary/50 shadow-[0_0_50px_rgba(255,121,0,0.15)] z-10 bg-[#25242b]">
                            <div class="absolute top-4 right-4 bg-gradient-to-r from-komik-primary to-komik-accent text-black text-[10px] font-bold px-3 py-1 rounded-full tracking-wider">
                                MOST POPULAR
                            </div>

                            <h3 class="text-2xl font-bold text-white mb-2">${plan.name}</h3>
                            <div class="flex items-baseline mb-2">
                                <span class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">
                                    IDR ${formatK(plan.price)}
                                </span>
                                <span class="text-gray-500 ml-2">/ ${plan.duration_months} bln</span>
                            </div>
                            <p class="text-xs text-komik-primary mb-6">${plan.max_devices} Devices Login</p>

                            <div class="plan-description space-y-4 mb-8 text-sm text-gray-300">
                                ${parseDesc(plan.description)}
                            </div>

                            <button onclick="openQrisModal('${plan.name}', ${plan.price}, ${plan.max_devices}, ${plan.duration_months}, 'standard')"
                                class="w-full py-4 rounded-xl btn-primary font-bold shadow-lg shadow-komik-primary/25 hover:shadow-komik-primary/50 transition-all transform hover:scale-[1.02]">
                                Get ${plan.name} Now
                            </button>
                        </div>`;
                    } else {
                        html = `
                        <div class="glass-card p-8 rounded-3xl relative overflow-hidden group hover:bg-white/5 transition-all duration-300">
                            <h3 class="text-xl font-semibold text-gray-300 mb-2">${plan.name}</h3>
                            <div class="flex items-baseline mb-6">
                                <span class="text-3xl font-bold text-white">IDR ${formatK(plan.price)}</span>
                                <span class="text-gray-500 ml-2 text-sm">/ ${plan.duration_months} bln</span>
                            </div>

                            <div class="plan-description space-y-4 mb-8 text-sm text-gray-400">
                                ${parseDesc(plan.description)}
                            </div>

                            <button onclick="openQrisModal('${plan.name}', ${plan.price}, ${plan.max_devices}, ${plan.duration_months}, 'standard')"
                                class="block w-full py-3 rounded-xl border border-white/20 hover:bg-white/10 text-white text-center transition-all font-medium">
                                Choose ${plan.name}
                            </button>
                        </div>`;
                    }
                    container.innerHTML += html;
                });
            }

            // Init
            fetchConfig(); // Load config async
            fetchPlans(); // Load plans async
            updatePrice(); // Show initial state immediately
        });
    </script>

    <!-- QRIS Modal for Pricing -->
    <div id="qrisModal" class="fixed inset-0 z-[60] hidden opacity-0 transition-opacity duration-300">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeQrisModal()"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div class="glass-card bg-[#1a1a1a] border border-white/10 rounded-3xl p-6 relative shadow-2xl scale-95 transition-transform duration-300 transform" id="qrisContent">
                <button onclick="closeQrisModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <i class="fas fa-times"></i>
                </button>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-komik-primary/10 text-komik-primary mb-4 text-xl">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-1">Scan QRIS</h3>
                    <p id="modalPlanName" class="text-komik-primary font-bold text-sm mb-2">Premium Access</p>
                    <p class="text-gray-400 text-xs mb-4">Scan kode di bawah ini untuk pembayaran.</p>

                    <div class="p-4 bg-white rounded-xl mx-auto max-w-[250px] mb-6 shadow-lg shadow-white/5">
                        <!-- Real QR Code -->
                        <img src="assets/images/qris_generated.png" alt="QRIS Code" class="w-full h-auto rounded-lg">
                    </div>

                    <!-- Validation Input -->
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10 text-left">
                        <label class="block text-gray-400 text-xs mb-2">Konfirmasi Pembayaran</label>
                        
                        <input type="text" id="waInput" placeholder="Nomor WA / Email" class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600 mb-3">

                        <div class="flex gap-2">
                             <input type="text" id="proofInput" placeholder="3-5 Digit Terakhir Bukti Transfer" class="flex-1 bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600">
                             <button id="submitBtn" onclick="submitOrder()" class="bg-komik-primary hover:bg-komik-primaryHover text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                 Kirim
                             </button>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2 italic">*Masukkan digit terakhir nomor referensi/struk untuk verifikasi.</p>
                    </div>

                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500 mt-6">
                        <i class="fas fa-check-circle text-komik-primary"></i>
                        <span>Verified Merchant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
            let SELECTED_PLAN = {
                name: '',
                price: 0,
                devices: 0,
                duration: 0,
                type: 'standard' // 'standard' or 'custom'
            };

            function openQrisModal(name, price, devices, duration, type) {
                const modal = document.getElementById('qrisModal');
                const content = document.getElementById('qrisContent');
                const planLabel = document.getElementById('modalPlanName');
                
                // Update State
                SELECTED_PLAN = {
                    name: name,
                    price: price,
                    devices: devices,
                    duration: duration,
                    type: type
                };
                
                if(name) {
                    planLabel.textContent = 'Pembayaran Paket ' + name;
                    planLabel.classList.remove('hidden');
                } else {
                    planLabel.classList.add('hidden');
                }

                modal.classList.remove('hidden');
                // Small delay to allow display:block to apply before opacity transition
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }, 10);
            }

        function closeQrisModal() {
            const modal = document.getElementById('qrisModal');
            const content = document.getElementById('qrisContent');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        async function submitOrder() {
            const wa = document.getElementById('waInput').value;
            const proof = document.getElementById('proofInput').value;
            const btn = document.getElementById('submitBtn');

            if(!wa || !proof) {
                alert('Mohon lengkapi Nomor WA dan Bukti Transfer');
                return;
            }

            // Get current plan config from State
            let planName = SELECTED_PLAN.name;
            let devices = SELECTED_PLAN.devices;
            let duration = SELECTED_PLAN.duration;
            let amount = SELECTED_PLAN.price;

            // If Custom (Ketengan), recalculate based on current slider values
            if (SELECTED_PLAN.type === 'custom') {
                 planName = 'Ketengan';
                 devices = parseInt(document.getElementById('deviceSlider').value);
                 // We need to get duration from the active button
                 const activeDurationBtn = document.querySelector('.duration-btn.active');
                 if(activeDurationBtn) duration = parseInt(activeDurationBtn.dataset.value);
                 
                 // Get price from simple parsing or global var logic
                 const priceText = document.getElementById('totalPrice').textContent;
                 amount = parseInt(priceText.replace(/[^0-9]/g, ''));
            }

            // Lock button
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            try {
                const response = await fetch('/api/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        plan_name: planName,
                        device_quota: devices,
                        duration_months: duration,
                        amount: amount,
                        customer_contact: wa,
                        proof_digits: proof
                    })
                });

                const result = await response.json();

                if(result.status === 'success') {
                    window.location.href = 'payment-success.html';
                } else {
                    // Extract message from data if available, or just use general error
                    const msg = result.data && result.data.message ? result.data.message : 'Unknown error';
                    alert('Error: ' + msg);
                    btn.disabled = false;
                    btn.textContent = 'Kirim';
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi.');
                btn.disabled = false;
                btn.textContent = 'Kirim';
            }
        }
    </script>
</body>

</html>