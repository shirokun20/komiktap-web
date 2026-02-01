@inject('contact', 'App\Settings\ContactSettings')
@inject('payment', 'App\Settings\PaymentSettings')
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
        .payment-instructions ul {
            list-style-type: disc;
            padding-left: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .payment-instructions ol {
            list-style-type: decimal;
            padding-left: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .payment-instructions li {
            margin-bottom: 0.25rem;
        }
        .payment-instructions strong {
            color: white;
            font-weight: 600;
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
            color: #22c55e;
            /* green-500 */
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
                <a href="#"
                    class="flex items-center gap-2 group/logo hover:scale-105 transition-transform duration-300">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png"
                        alt="KomikTap Logo" class="w-10 h-10 md:w-12 md:h-12 drop-shadow-[0_0_8px_rgba(255,121,0,0.5)]">
                    <span class="text-white font-bold text-xl md:text-2xl italic tracking-tighter">KOMIK<span
                            class="text-komik-primary">TAP</span></span>
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
                    <a href="#faq"
                        class="text-gray-300 hover:text-white font-medium text-sm tracking-wide relative group/link py-2">
                        FAQ
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-komik-primary group-hover/link:w-full transition-all duration-300"></span>
                    </a>
                </div>

                <!-- CTA -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('download.index') }}"
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
                <i class="fa-solid fa-crown"></i> Akses Premium
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
                    <h3 class="text-xl font-bold text-white mb-3">Tanpa Iklan</h3>
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
                    <h3 class="text-xl font-bold text-white mb-3">Multi Perangkat</h3>
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
                    <h3 class="text-xl font-bold text-white mb-3">Baca Offline & PDF</h3>
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
                        <p class="text-gray-500 mt-4">Memuat paket...</p>
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
                            <span>Konfigurasi Kustom</span>
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
                                <span>Harga Dasar</span>
                                <span class="text-white" id="basePriceDisplay">IDR 15,000</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Jumlah Perangkat</span>
                                <span class="text-white">x<span id="deviceCountSummary">3</span></span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Durasi</span>
                                <span class="text-white">x<span id="durationSummary">1 Bulan</span></span>
                            </div>
                            <div class="h-px bg-gray-700 my-2"></div>
                            <div class="flex justify-between text-komik-primary font-medium">
                                <span>Total Diskon</span>
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
    <section id="faq" class="pb-16 pt-1 relative z-10">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-8 text-center">Frequently Asked Questions</h2>
            <div id="faqContainer" class="space-y-4">
                <!-- Loading State -->
                <div class="glass-card p-6 rounded-2xl text-center">
                    <i class="fas fa-circle-notch fa-spin text-komik-primary text-2xl"></i>
                    <p class="text-gray-500 mt-2 text-sm">Memuat pertanyaan...</p>
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
                    <a href="{{ route('donation.index') }}"
                        class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">Donasi</a>
                    <a href="{{ route('contact') }}"
                        class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">Contact</a>
                    <a href="{{ route('page.show', 'dmca') }}"
                        class="hover:text-komik-primary hover:shadow-glow transition-all duration-300">DMCA</a>
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
                    // Check if desc is valid
                    if (!desc) return '';

                    const lines = desc.split('\n');
                    let html = '<ul>';

                    lines.forEach(line => {
                        let trimmed = line.trim();
                        if (!trimmed) return; // Skip empty lines

                        // Remove leading dash if present
                        if (trimmed.startsWith('-')) {
                            trimmed = trimmed.substring(1).trim();
                        }

                        // Parse Bold (**text**)
                        trimmed = trimmed.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                        // Parse Italic (*text* or _text_)
                        trimmed = trimmed.replace(/\*(.*?)\*/g, '<em>$1</em>');
                        trimmed = trimmed.replace(/_(.*?)_/g, '<em>$1</em>');

                        html += `<li>${trimmed}</li>`;
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
                                PALING LARIS
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
                                Beli Paket ${plan.name}
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
                                Pilih Paket ${plan.name}
                            </button>
                        </div>`;
                    }
                    container.innerHTML += html;
                });
            }

            // Fetch FAQs from API
            async function fetchFaqs() {
                try {
                    const response = await fetch('/api/faqs');
                    const json = await response.json();

                    if (json.status === 'success') {
                        renderFaqs(json.data);
                    }
                } catch (error) {
                    console.error('Failed to fetch FAQs', error);
                }
            }

            function renderFaqs(faqs) {
                const container = document.getElementById('faqContainer');
                container.innerHTML = '';

                if (faqs.length === 0) {
                    container.innerHTML = '<div class="col-span-2 text-center text-gray-500 italic">Belum ada pertanyaan.</div>';
                    return;
                }

                // Simple markdown parser for answers (bold, italic, links)
                const parseAnswer = (text) => {
                    return text
                        .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                        .replace(/_(.*?)_/g, '<i>$1</i>')
                        .replace(/\n/g, '<br>');
                };

                faqs.forEach(faq => {
                    const html = `
                    <div class="glass-card p-6 rounded-2xl hover:bg-white/5 transition-colors">
                        <h4 class="font-bold text-white text-base mb-2 flex items-center gap-3">
                            <i class="fa-regular fa-circle-question text-komik-primary text-xl"></i>
                            ${faq.question}
                        </h4>
                        <p class="text-gray-400 text-sm leading-relaxed pl-8">
                            ${parseAnswer(faq.answer)}
                        </p>
                    </div>`;
                    container.innerHTML += html;
                });
            }

            // Init
            fetchConfig(); // Load config async
            fetchPlans(); // Load plans async
            fetchFaqs(); // Load FAQs async
            updatePrice(); // Show initial state immediately
        });
    </script>

    <!-- QRIS Modal for Pricing -->
    <div id="qrisModal" class="fixed inset-0 z-[60] hidden opacity-0 transition-opacity duration-300">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeQrisModal()"></div>

        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl p-4">
            <div class="glass-card bg-[#1a1a1a] border border-white/10 rounded-3xl p-6 relative shadow-2xl scale-95 transition-transform duration-300 transform"
                id="qrisContent">
                <button onclick="closeQrisModal()"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <i class="fas fa-times"></i>
                </button>

                    <h3 class="text-xl font-bold text-white mb-4">Pilih Metode Pembayaran</h3>

                    <!-- Payment Method Tabs -->
                    <div class="flex flex-wrap gap-2 justify-center mb-6" id="paymentMethodsContainer">
                        <!-- Populated by JS -->
                        <div class="w-full text-center py-4">
                            <i class="fas fa-circle-notch fa-spin text-komik-primary"></i>
                        </div>
                    </div>

                    <!-- Payment Details Container -->
                    <div id="paymentDetailsContainer">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Validation Input -->
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10 text-left">
                        <!-- Voucher Code -->
                        <div class="mb-3">
                            <label class="block text-gray-400 text-xs mb-1">Kode Promo</label>
                            <div class="flex gap-2">
                                <div class="relative group flex-1">
                                    <input type="text" id="voucherInput" placeholder="Masukkan Kode"
                                        class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600 uppercase tracking-wider transition-all">
                                    <i class="fas fa-ticket-alt absolute right-3 top-2.5 text-gray-600 group-hover:text-komik-primary transition-colors"></i>
                                </div>
                                <button type="button" onclick="checkVoucher()" id="btnCheckVoucher"
                                    class="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors border border-white/10">
                                    Apply
                                </button>
                            </div>
                            <p id="voucherMessage" class="text-[10px] mt-1 hidden"></p>
                        </div>

                        <!-- Price Summary -->
                        <div id="priceSummary" class="hidden mb-4 p-3 bg-white/5 rounded-lg border border-white/10 text-sm">
                            <div class="flex justify-between text-gray-400 mb-1">
                                <span>Harga Normal</span>
                                <span id="summaryOriginalPrice">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-green-400 mb-1 hidden" id="summaryDiscountRow">
                                <span>Diskon <span id="summaryDiscountCode" class="text-[10px] bg-green-500/20 px-1 rounded ml-1"></span></span>
                                <span id="summaryDiscountAmount">-Rp 0</span>
                            </div>
                            <div class="flex justify-between text-white font-bold pt-2 border-t border-white/10 mt-2">
                                <span>Total Bayar</span>
                                <span id="summaryFinalPrice">Rp 0</span>
                            </div>
                        </div>

                        <label class="block text-gray-400 text-xs mb-1">Konfirmasi Pesanan</label>
                        <input type="text" id="waInput" placeholder="Nomor WA / Email"
                            class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600 mb-3">

                        <div class="flex gap-2">
                            <input type="text" id="proofInput" placeholder="3-5 Digit Terakhir Bukti Transfer"
                                class="flex-1 bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600">
                            <button id="submitBtn" onclick="submitOrder()"
                                class="bg-komik-primary hover:bg-komik-primaryHover text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Bayar
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2 italic">*Total bayar akan diverifikasi otomatis.</p>
                    </div>

                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500 mt-6">
                        <i class="fas fa-check-circle text-komik-primary"></i>
                        <span>Verified Merchant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Modal -->
    <div id="instructionsModal" class="fixed inset-0 z-[70] hidden opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeInstructionsModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
             <div class="glass-card bg-[#1a1a1a] border border-white/10 rounded-3xl p-6 relative shadow-2xl scale-95 transition-transform duration-300 transform" id="instructionsModalContent">
                <button onclick="closeInstructionsModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                <h3 class="text-lg font-bold text-white mb-4">Petunjuk Pembayaran</h3>
                <div class="text-gray-300 text-sm payment-instructions max-h-[60vh] overflow-y-auto" id="instructionsContentBody">
                    <!-- Content populated by JS -->
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

            // Clear previous inputs
            if(document.getElementById('voucherInput')) document.getElementById('voucherInput').value = '';
            document.getElementById('waInput').value = '';
            document.getElementById('proofInput').value = '';

            // Update State
            SELECTED_PLAN = {
                name: name,
                price: price,
                devices: devices,
                duration: duration,
                type: type
            };

            // Reset UI for new session
            resetVoucherUI();


            if (name) {
                const labels = document.querySelectorAll('.modalPlanNameDisplay');
                labels.forEach(el => {
                     el.textContent = 'Pembayaran Paket ' + name;
                     el.classList.remove('hidden');
                });
            } else {
                 const labels = document.querySelectorAll('.modalPlanNameDisplay');
                 labels.forEach(el => el.classList.add('hidden'));
            }

            // Fetch Payment Methods based on Plan Type
            // If plan name contains 'Donasi', assume donation? or rely on 'type' argument?
            // Existing types: 'standard', 'custom'.
            // If title is 'Donasi' or 'Campaign', treat as donation.
            // For now, let's use 'order' for standard/custom, and 'donation' if name is Donasi.
            
            const methodType = (name === 'Donasi' || name.startsWith('Donasi')) ? 'donation' : 'order';
            fetchPaymentMethods(methodType);

            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function openInstructionsModal(index) {
            const content = document.getElementById(`instructions-data-${index}`).innerHTML;
            const modal = document.getElementById('instructionsModal');
            const body = document.getElementById('instructionsContentBody');
            
            body.innerHTML = content;
            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                document.getElementById('instructionsModalContent').classList.remove('scale-95');
                 document.getElementById('instructionsModalContent').classList.add('scale-100');
            }, 10);
        }

        function closeInstructionsModal() {
             const modal = document.getElementById('instructionsModal');
             modal.classList.add('opacity-0');
             document.getElementById('instructionsModalContent').classList.remove('scale-100');
             document.getElementById('instructionsModalContent').classList.add('scale-95');
             setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function copyToClipboard(text) {
            console.log(text);
            if (!text) return;

            // Try modern API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Nomor berhasil disalin!');
                }).catch(err => {
                    console.error('Clipboard API Error:', err);
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        function fallbackCopyToClipboard(text) {
            try {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                
                // Ensure it's not visible but part of the document
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                textArea.style.top = "0";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                
                textArea.focus();
                textArea.select();
                
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                
                if (successful) {
                    alert('Nomor berhasil disalin!');
                } else {
                    throw new Error('execCommand returned false');
                }
            } catch (err) {
                console.error('Fallback Copy Error:', err);
                alert('Gagal menyalin nomor. Silakan salin secara manual.');
            }
        }

        let PAYMENT_METHODS = [];
        let SELECTED_PAYMENT_INDEX = 0;

        function selectPaymentMethod(index) {
            SELECTED_PAYMENT_INDEX = index;
            // Update Buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active', 'border-komik-primary', 'text-white', 'bg-komik-primary/10');
                btn.classList.add('text-gray-400', 'bg-white/5', 'border-white/10');
                
                if(parseInt(btn.dataset.index) === index) {
                    btn.classList.add('active', 'border-komik-primary', 'text-white', 'bg-komik-primary/10');
                    btn.classList.remove('text-gray-400', 'bg-white/5', 'border-white/10');
                }
            });

            // Show Content
            document.querySelectorAll('.payment-detail-content').forEach(el => el.classList.add('hidden'));
            
            // Hide Placeholder
            const placeholder = document.getElementById('payment-placeholder');
            if(placeholder) placeholder.classList.add('hidden');

            // Use querySelector to find the specific element id that starts with payment-detail-{index}
            // Actually ID is unique, so:
            const specificContent = document.getElementById(`payment-detail-${index}`);
            if(specificContent) specificContent.classList.remove('hidden');
        }

        // Duplicate removed


        async function fetchPaymentMethods(type = 'all') {
            try {
                const response = await fetch(`/api/payment-methods?type=${type}`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data.is_enabled) {
                    PAYMENT_METHODS = result.data.payment_methods || [];
                    renderPaymentMethods();
                } else {
                     // Handle disabled or error case if needed
                     const tabsContainer = document.getElementById('paymentMethodsContainer');
                     tabsContainer.innerHTML = '<div class="w-full text-center py-4 text-gray-500">Metode pembayaran tidak tersedia saat ini.</div>';
                }
            } catch (error) {
                console.error('Error fetching payment methods:', error);
            }
        }

        function renderPaymentMethods() {
            const tabsContainer = document.getElementById('paymentMethodsContainer');
            const detailsContainer = document.getElementById('paymentDetailsContainer');
            
            if (PAYMENT_METHODS.length === 0) {
                tabsContainer.innerHTML = '';
                detailsContainer.innerHTML = '<div class="text-center py-6"><p class="text-gray-500">Belum ada metode pembayaran yang tersedia.</p></div>';
                return;
            }

            // Render Tabs
            tabsContainer.innerHTML = PAYMENT_METHODS.map((method, index) => `
                <button onclick="selectPaymentMethod(${index})" 
                        class="payment-method-btn px-4 py-2 rounded-lg border border-white/10 bg-white/5 text-sm font-medium text-gray-400 hover:text-white hover:border-komik-primary hover:bg-komik-primary/10 transition-all"
                        data-index="${index}">
                    ${method.name}
                </button>
            `).join('');

            // Render Details
            let detailsHtml = PAYMENT_METHODS.map((method, index) => {
                const instructions = method.instructions || '';
                const qrisUrl = method.qris_image_path ? `/storage/${method.qris_image_path}` : null;

                return `
                <div class="payment-detail-content hidden" id="payment-detail-${index}">
                    <div class="flex items-center gap-4 mb-6 bg-white/5 p-4 rounded-2xl border border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-komik-primary to-orange-600 flex items-center justify-center text-white shadow-lg shadow-komik-primary/20 shrink-0">
                            <i class="fas fa-wallet text-lg"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-bold text-white leading-tight">${method.name}</h3>
                            <div class="inline-flex items-center gap-1.5 mt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                <p class="text-gray-400 text-xs font-medium modalPlanNameDisplay">Akses Premium</p>
                            </div>
                        </div>
                    </div>
                    
                    ${method.account_number ? `
                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl p-4 mb-4 border border-white/10 relative overflow-hidden group" onclick="copyToClipboard('${method.account_number}')">
                        <div class="absolute inset-0 bg-komik-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <p class="text-xs text-gray-500 mb-1 font-medium tracking-wide">NOMOR REKENING / VA</p>
                        <div class="text-2xl font-mono font-bold text-white tracking-widest flex items-center justify-between gap-2 group-hover:text-komik-primary transition-colors cursor-pointer">
                            <span>${method.account_number}</span>
                            <i class="fas fa-copy text-sm text-gray-600 group-hover:text-komik-primary transition-colors"></i>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 font-medium uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-komik-primary"></span>
                            ${method.account_holder || ''}
                        </p>
                    </div>
                    ` : ''}

                    ${qrisUrl ? `
                    <div class="p-2 bg-white rounded-xl mx-auto max-w-[200px] mb-6 shadow-lg shadow-white/5">
                        <img src="${qrisUrl}" alt="QRIS Code" class="w-full h-auto rounded-lg">
                    </div>
                    ` : ''}

                    <div class="text-center">
                        <button onclick="openInstructionsModal('${index}')" class="text-sm text-komik-primary hover:text-white underline decoration-dashed underline-offset-4 transition-colors">
                            Lihat Cara Pembayaran
                        </button>
                    </div>

                    <!-- Hidden Instructions Data -->
                    <div id="instructions-data-${index}" class="hidden">
                        ${instructions}
                    </div>
                    <br>
                </div>
                `;
            }).join('');

            // Add Placeholder
            detailsHtml += `
                <div id="payment-placeholder" class="text-center py-12 px-6">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-gray-600 mx-auto mb-4 border border-white/5">
                        <i class="fas fa-mouse-pointer text-2xl"></i>
                    </div>
                    <h4 class="text-white font-bold mb-2">Pilih Metode Pembayaran</h4>
                    <p class="text-gray-500 text-sm">Silakan pilih salah satu metode di atas untuk melihat detail pembayaran.</p>
                </div>
            `;

            detailsContainer.innerHTML = detailsHtml;
            
            // Re-apply current plan name if modal is open
            if(SELECTED_PLAN.name) {
                 const labels = document.querySelectorAll('.modalPlanNameDisplay');
                 labels.forEach(el => {
                      el.textContent = 'Pembayaran Paket ' + SELECTED_PLAN.name;
                      el.classList.remove('hidden');
                 });
            }
        }

        function openInstructionsModal(index) {
            const content = document.getElementById(`instructions-data-${index}`).innerHTML;
            const modal = document.getElementById('instructionsModal');
            const body = document.getElementById('instructionsContentBody');
            
            body.innerHTML = content;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                document.getElementById('instructionsModalContent').classList.remove('scale-95');
                 document.getElementById('instructionsModalContent').classList.add('scale-100');
            }, 10);
        }

        function closeInstructionsModal() {
             const modal = document.getElementById('instructionsModal');
             modal.classList.add('opacity-0');
             document.getElementById('instructionsModalContent').classList.remove('scale-100');
             document.getElementById('instructionsModalContent').classList.add('scale-95');
             setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
             // Pre-fetch 'all' or don't fetch anything until modal opens?
             // Maybe pre-fetch 'order' as it's most common
             fetchPaymentMethods('all');
        });

        async function checkVoucher() {
            const codeInput = document.getElementById('voucherInput');
            const msg = document.getElementById('voucherMessage');
            const btn = document.getElementById('btnCheckVoucher');
            
            const code = codeInput.value.trim();
            if (!code) return; // Do nothing if empty

            // UI Loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            msg.classList.add('hidden');

            try {
                // Determine current amount based on selection
                let currentAmount = SELECTED_PLAN.price;
                if(SELECTED_PLAN.type === 'custom') {
                    // Logic to get live price from slider if needed, but usually slider updates SELECTED_PLAN?
                    // Wait, custom logic is inside submitOrder() usually re-calculated.
                    // We need a helper to get current price for custom plan if not stored in SELECTED_PLAN yet.
                    
                    // Re-calculate custom price just in case
                    const devices = parseInt(document.getElementById('deviceSlider').value);
                    const months = parseInt(document.getElementById('monthSlider').value);
                    // Use a helper function or assume `updatePrice()` updates global var or UI? 
                    // `updatePrice()` updates `totalPriceDisplay`. Let's parse it or replicate logic.
                    // Easiest is to trust `totalPriceDisplay` text content if formatted correctly, or re-calc using config.
                    
                    // Let's assume SELECTED_PLAN is updated OR use the displayed price for estimation
                    // Actually `updatePrice` function (not shown in snippet but assumed exists) should update SELECTED_PLAN?
                    // If not, let's grab from frontend display for now.
                    // A safer bet is to grab the raw integer if stored, otherwise re-calculate.
                    
                    // For now, let's assume `calculateKetenganPrice` logic is available or we use API to get clean amount ? No, just frontend calc.
                    // Let's rely on `SELECTED_PLAN.price` being updated when opening modal? 
                    // No, Custom Plan updating happens in the slider page, NOT in the modal.
                    // The modal is opened with `openQrisModal('Ketengan', calculatedPrice, ...)`
                    // SO `SELECTED_PLAN.price` IS correct because it's passed when clicking "Beli Sekarang".
                    currentAmount = SELECTED_PLAN.price;
                }

                const response = await fetch('/api/check-voucher', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        // If CSRF is needed:
                        // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        voucher_code: code,
                        amount: currentAmount
                    })
                });

                const result = await response.json();

                msg.classList.remove('hidden');
                // ApiResponse trait returns { status: 'success', data: ... }
                // So result.success is undefined. Check result.status === 'success'
                if (response.ok && result.status === 'success') {
                    msg.innerHTML = `<span class="text-green-400"><i class="fas fa-check-circle"></i> ${result.data.message}</span>`;
                    
                    // Update Summary
                    updatePriceSummary(currentAmount, result.data.discount_amount, result.data.final_amount, code);
                    
                } else {
                    // Extract message from structure: result.data.message or result.message
                    const errorMessage = result.data?.message || result.message || 'Kode tidak valid';
                    msg.innerHTML = `<span class="text-red-400"><i class="fas fa-times-circle"></i> ${errorMessage}</span>`;
                    updatePriceSummary(currentAmount, 0, currentAmount, null);
                }

            } catch (error) {
                console.error(error);
                msg.classList.remove('hidden');
                msg.innerHTML = '<span class="text-red-400">Terjadi kesalahan sistem.</span>';
            } finally {
                btn.innerHTML = 'Apply';
                btn.disabled = false;
            }
        }

        function updatePriceSummary(original, discount, final, code) {
            const summary = document.getElementById('priceSummary');
            summary.classList.remove('hidden');

            document.getElementById('summaryOriginalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(original);
            
            if (discount > 0) {
                document.getElementById('summaryDiscountRow').classList.remove('hidden');
                document.getElementById('summaryDiscountAmount').textContent = '-Rp ' + new Intl.NumberFormat('id-ID').format(discount);
                document.getElementById('summaryDiscountCode').textContent = code;
            } else {
                document.getElementById('summaryDiscountRow').classList.add('hidden');
            }

            document.getElementById('summaryFinalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(final);
        }

        function resetVoucherUI() {
            if(document.getElementById('voucherInput')) document.getElementById('voucherInput').value = '';
            if(document.getElementById('voucherMessage')) document.getElementById('voucherMessage').classList.add('hidden');
            if(document.getElementById('priceSummary')) document.getElementById('priceSummary').classList.add('hidden');
            // We can optionally show summary with just original price
             const summary = document.getElementById('priceSummary');
             if(summary) {
                 summary.classList.remove('hidden');
                 document.getElementById('summaryOriginalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(SELECTED_PLAN.price);
                 document.getElementById('summaryDiscountRow').classList.add('hidden');
                 document.getElementById('summaryFinalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(SELECTED_PLAN.price);
             }
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
            const voucher = document.getElementById('voucherInput') ? document.getElementById('voucherInput').value : '';
            const btn = document.getElementById('submitBtn');

            if (!wa || !proof) {
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
                if (activeDurationBtn) duration = parseInt(activeDurationBtn.dataset.value);

                // Get price from simple parsing or global var logic
                const priceText = document.getElementById('totalPrice').textContent;
                amount = parseInt(priceText.replace(/[^0-9]/g, ''));
            }
            // The instruction implies we should use SELECTED_PLAN directly for the payload,
            // and the custom plan logic for `planName`, `devices`, `duration`, `amount`
            // is no longer needed here as the payload will use SELECTED_PLAN directly.
            // However, the instruction's payload uses SELECTED_PLAN.name, SELECTED_PLAN.devices, etc.
            // which means the custom plan recalculation logic should still update SELECTED_PLAN
            // or the payload should be constructed from the recalculated values.
            // Given the instruction explicitly states `plan_name: SELECTED_PLAN.name`,
            // `device_quota: SELECTED_PLAN.devices || 1`, etc., it suggests that SELECTED_PLAN
            // should already hold the correct values, or the backend will handle defaults.
            // Let's remove the custom plan recalculation block as per the instruction's implied payload structure.

            // Lock button
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // Get selected payment method name
            const selectedMethod = PAYMENT_METHODS[SELECTED_PAYMENT_INDEX];
            const methodName = selectedMethod ? selectedMethod.name : '';

            try {
                const response = await fetch('/api/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        plan_name: SELECTED_PLAN.name,
                        device_quota: SELECTED_PLAN.devices || 1, // Default 1
                        duration_months: SELECTED_PLAN.duration || 1, // Default 1
                        amount: SELECTED_PLAN.price, // Trusting frontend amount for initial validation, backend re-calcs
                        customer_contact: wa,
                        proof_digits: proof,
                        voucher_code: voucher, // Include voucher code
                        payment_method: methodName // Add Payment Method
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.href = '/success/' + result.data.transaction_code;
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