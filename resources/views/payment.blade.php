<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - KomikTap Premium</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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
                    }
                }
            }
        }
    </script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #0f0e13;
            color: #b8b8b8;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

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
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
        }

        .glass-card:hover {
            border-color: rgba(255, 121, 0, 0.2);
            box-shadow: 0 12px 48px 0 rgba(0, 0, 0, 0.5);
            transform: translateY(-2px);
        }

        .glow-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }

        .detail-glass-card {
            border-bottom: 2px solid rgba(255, 121, 0, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37), 0 0 50px rgba(255, 121, 0, 0.06);
        }

        .detail-glass-card:hover {
            border-bottom-color: rgba(255, 121, 0, 0.35);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.5), 0 0 60px rgba(255, 121, 0, 0.1);
        }

        .text-gradient {
            background: linear-gradient(135deg, #ff7900 0%, #FFD400 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff7900 0%, #ff5e00 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 24px rgba(255, 121, 0, 0.25);
            position: relative;
            overflow: hidden;
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 6px 32px rgba(255, 121, 0, 0.45);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .method-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .method-card:hover {
            background: rgba(255, 121, 0, 0.07);
            border-color: rgba(255, 121, 0, 0.35);
        }

        .method-card.active {
            background: rgba(255, 121, 0, 0.10);
            border-color: #ff7900;
            box-shadow: 0 4px 20px rgba(255, 121, 0, 0.2), 0 0 0 1px rgba(255, 121, 0, 0.3);
        }

        .method-card.active .method-radio {
            background: #ff7900;
            border-color: #ff7900;
        }

        .method-card.active .method-radio::after {
            opacity: 1;
        }

        .method-radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
            position: relative;
        }

        .method-radio::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .detail-panel {
            display: none;
            animation: fadeSlideUp 0.3s ease-out;
        }

        .detail-panel.active {
            display: block;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .account-copy-card {
            background: linear-gradient(135deg, rgba(255, 121, 0, 0.06) 0%, rgba(30, 29, 37, 0.9) 100%);
            border: 1px solid rgba(255, 121, 0, 0.2);
            transition: all 0.25s;
            cursor: pointer;
        }

        .account-copy-card:hover {
            border-color: rgba(255, 121, 0, 0.5);
            background: linear-gradient(135deg, rgba(255, 121, 0, 0.12) 0%, rgba(30, 29, 37, 0.9) 100%);
        }

        .account-copy-card:active {
            transform: scale(0.99);
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: rgba(30, 29, 37, 0.95);
            border: 1px solid rgba(255, 121, 0, 0.4);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 9999;
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
        }

        .step-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s;
        }

        .step-dot.active {
            width: 24px;
            border-radius: 4px;
            background: #ff7900;
        }

        .image-zoom-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .image-zoom-overlay.show {
            display: flex;
        }

        .shimmer {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.03) 25%, rgba(255, 255, 255, 0.07) 50%, rgba(255, 255, 255, 0.03) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .amount-badge {
            background: linear-gradient(135deg, rgba(255, 121, 0, 0.15) 0%, rgba(255, 212, 0, 0.08) 100%);
            border: 1px solid rgba(255, 121, 0, 0.25);
        }

        .voucher-input-wrap {
            position: relative;
        }

        .voucher-input-wrap input {
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ff7900;
            border-radius: 2px;
        }
    </style>
</head>

<body class="antialiased font-sans selection:bg-komik-primary selection:text-white min-h-screen">

    <!-- Background -->
    <div class="fixed inset-0 bg-grid opacity-20 pointer-events-none -z-10"></div>
    <div
        class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-[#ff7900]/8 rounded-full blur-[150px] -z-10 pointer-events-none">
    </div>
    <!-- Ambient glow blobs -->
    <div class="glow-blob fixed w-[600px] h-[600px] bg-[#ff7900]/5 top-[-200px] left-[-200px] -z-10"></div>
    <div class="glow-blob fixed w-[400px] h-[400px] bg-[#FFD400]/4 bottom-[-100px] right-[-100px] -z-10"></div>

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-[#0f0e13]/85 backdrop-blur-xl border-b border-white/5">
        <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#ff7900]/40 to-transparent">
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-3 text-gray-400 hover:text-white transition-colors group">
                    <div
                        class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-white/10 transition-colors">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </div>
                    <span class="text-sm font-medium hidden sm:block">Kembali ke Beranda</span>
                </a>

                <div class="flex items-center gap-2">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="Logo"
                        class="w-7 h-7">
                    <span class="text-base font-black text-white">KOMIK<span class="text-[#ff7900]">TAP</span></span>
                </div>

                <!-- Step indicator -->
                <div class="step-indicator">
                    <div class="step-dot" id="dot-1"></div>
                    <div class="step-dot" id="dot-2"></div>
                    <div class="step-dot" id="dot-3"></div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main class="pt-20 pb-16 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

            <!-- Page Header -->
            <div class="mb-8 text-center sm:text-left">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff7900]/10 border border-[#ff7900]/20 text-[#ff7900] text-xs font-semibold tracking-wider mb-3">
                    <i class="fas fa-shield-halved"></i>
                    <span>Pembayaran Aman</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">Pilih Metode Pembayaran</h1>
                <p class="text-gray-400 text-sm mt-1 max-w-md">Pilih metode yang paling nyaman untuk menyelesaikan
                    pesanan Anda.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- LEFT: Order Summary + Method List -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Order Summary Card -->
                    <div class="glass-card rounded-2xl p-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Ringkasan Pesanan
                        </p>

                        <!-- Plan icon + name -->
                        <div class="flex items-start gap-3 mb-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-[#ff7900]/10 border border-[#ff7900]/20 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-crown text-[#ff7900]"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-semibold text-sm leading-snug" id="summaryPlanName">Memuat...
                                </p>
                                <p class="text-gray-500 text-xs mt-0.5" id="summaryPlanMeta">-</p>
                            </div>
                        </div>

                        <!-- Price rows -->
                        <div class="border-t border-white/5 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-400" id="originalPriceRow">
                                <span>Harga Normal</span>
                                <span id="originalPriceDisplay">-</span>
                            </div>
                            <div class="flex justify-between text-green-400 hidden" id="discountRow">
                                <span>Diskon <span id="voucherCodeBadge"
                                        class="text-[10px] bg-green-500/20 px-1.5 py-0.5 rounded ml-1"></span></span>
                                <span id="discountAmountDisplay">-</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-white/5">
                                <span class="text-gray-400">Total Bayar</span>
                                <div class="amount-badge px-3 py-1 rounded-full">
                                    <span class="text-[#ff7900] font-bold text-lg" id="finalPriceDisplay">IDR 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher / Promo Code -->
                    <div class="glass-card rounded-2xl p-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Kode Promo</p>
                        <div class="flex gap-2">
                            <div class="voucher-input-wrap flex-1 relative">
                                <input type="text" id="voucherInput" placeholder="Masukkan kode promo"
                                    class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#ff7900]/50 focus:ring-1 focus:ring-[#ff7900]/20 transition-all">
                                <i class="fas fa-ticket-alt absolute right-3 top-2.5 text-gray-600"></i>
                            </div>
                            <button type="button" onclick="checkVoucher()" id="btnCheckVoucher"
                                class="bg-white/8 hover:bg-white/15 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors border border-white/10 whitespace-nowrap">
                                Terapkan
                            </button>
                        </div>
                        <p id="voucherMessage" class="text-xs mt-2 hidden"></p>
                    </div>

                    <!-- Payment Methods List -->
                    <div class="glass-card rounded-2xl p-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Metode Pembayaran
                        </p>

                        <!-- Skeleton -->
                        <div id="methodsSkeleton" class="space-y-3">
                            <div class="shimmer h-14 rounded-xl"></div>
                            <div class="shimmer h-14 rounded-xl"></div>
                            <div class="shimmer h-14 rounded-xl"></div>
                        </div>

                        <!-- Methods container -->
                        <div id="methodsList" class="space-y-3 hidden"></div>

                        <!-- Empty state -->
                        <div id="methodsEmpty" class="hidden text-center py-6">
                            <i class="fas fa-wallet text-2xl text-gray-600 mb-2"></i>
                            <p class="text-gray-500 text-sm">Tidak ada metode pembayaran tersedia.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Payment Detail Panel + Confirmation Form -->
                <div class="lg:col-span-3">
                    <div class="glass-card rounded-2xl p-6 lg:sticky lg:top-24 detail-glass-card">

                        <!-- Placeholder state -->
                        <div id="placeholderState" class="text-center py-12">
                            <div
                                class="w-20 h-20 rounded-2xl bg-white/4 border border-white/6 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-credit-card text-3xl text-gray-600"></i>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Pilih Metode Terlebih Dahulu</h3>
                            <p class="text-gray-500 text-sm leading-relaxed max-w-xs mx-auto">Pilih salah satu metode
                                pembayaran di sebelah kiri untuk melihat detail dan instruksi pembayaran.</p>
                        </div>

                        <!-- Detail panels (populated by JS) -->
                        <div id="detailPanels"></div>

                        <!-- Confirmation Form -->
                        <div id="confirmationForm" class="hidden mt-6 pt-6 border-t border-white/6">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Konfirmasi
                                Pembayaran</p>

                            <div class="space-y-3 mb-4">
                                <div>
                                    <label class="text-gray-400 text-xs block mb-1.5 font-medium">Nomor WA / Email <span
                                            class="text-gray-600">(Opsional)</span></label>
                                    <input type="text" id="waInput" placeholder="cth: 08123456789 atau email@domain.com"
                                        class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#ff7900]/50 focus:ring-1 focus:ring-[#ff7900]/20 transition-all">
                                </div>

                                <div>
                                    <label class="text-gray-400 text-xs block mb-1.5 font-medium">
                                        3-5 Digit Terakhir Referensi Transfer
                                        <span class="text-[#ff7900]">*</span>
                                    </label>
                                    <input type="text" id="proofInput" maxlength="8" placeholder="cth: 12345"
                                        class="w-full bg-black/20 border border-white/10 rounded-xl mb-3 px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-[#ff7900]/50 focus:ring-1 focus:ring-[#ff7900]/20 transition-all font-mono tracking-widest mb-2">
                                    <button onclick="submitOrder()" id="submitBtn"
                                        class="btn-primary w-full text-white px-5 py-3 rounded-xl text-sm font-bold">
                                        Konfirmasi
                                    </button>
                                    <p class="text-gray-600 text-[11px] mt-2 leading-relaxed">
                                        <i class="fas fa-info-circle mr-1 text-[#ff7900]/50"></i>
                                        Masukkan 3-5 digit terakhir nomor referensi/struk untuk verifikasi pesanan Anda.
                                    </p>
                                </div>
                            </div>

                            <!-- Security badge -->
                            <div class="flex items-center gap-2 text-gray-600 text-xs">
                                <i class="fas fa-lock text-[#ff7900]/40"></i>
                                <span>Data Anda diproses dengan aman &amp; terenkripsi</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Image Zoom Overlay -->
    <div id="imageZoomOverlay" class="image-zoom-overlay" onclick="closeImageZoom()">
        <button onclick="closeImageZoom()"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <img id="zoomedImage" src="" alt="QRIS" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl object-contain">
    </div>

    <!-- Instructions Drawer -->
    <div id="instructionsDrawer" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeInstructions()"></div>
        <div class="absolute bottom-0 inset-x-0 max-h-[80vh] bg-[#1a1924] border-t border-white/8 rounded-t-3xl overflow-hidden flex flex-col shadow-2xl"
            style="transform: translateY(100%); transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);"
            id="instructionsDrawerContent">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/6 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#ff7900]/15 flex items-center justify-center">
                        <i class="fas fa-list-check text-[#ff7900] text-sm"></i>
                    </div>
                    <h3 class="text-white font-bold">Cara Pembayaran</h3>
                </div>
                <button onclick="closeInstructions()"
                    class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 px-6 py-5 text-sm text-gray-300 leading-relaxed space-y-3"
                id="instructionsBody">
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast flex items-center gap-2">
        <i class="fas fa-check-circle text-[#ff7900]"></i>
        <span id="toastMsg"></span>
    </div>

    <script>
        // ====================================
        // Parse query params
        // ====================================
        const params = new URLSearchParams(window.location.search);
        const PLAN_NAME = params.get('plan') || '';
        const PLAN_AMOUNT = parseInt(params.get('amount') || '0');
        const PLAN_DEVICES = parseInt(params.get('devices') || '1');
        const PLAN_DURATION = parseInt(params.get('duration') || '1');
        const PLAN_TYPE = params.get('type') || 'order'; // 'order' | 'custom'

        let PAYMENT_METHODS = [];
        let SELECTED_INDEX = -1;
        let VOUCHER_CODE = '';
        let FINAL_AMOUNT = PLAN_AMOUNT;

        const fmt = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 4 }).format(n);

        // ====================================
        // Init
        // ====================================
        document.addEventListener('DOMContentLoaded', () => {
            if (!PLAN_NAME || PLAN_AMOUNT < 1000) {
                window.location.href = '/';
                return;
            }

            // Populate summary
            document.getElementById('summaryPlanName').textContent = 'Paket ' + PLAN_NAME;
            let meta = PLAN_DEVICES + ' Device' + (PLAN_DEVICES > 1 ? 's' : '');
            if (PLAN_DURATION > 0) meta += ' · ' + PLAN_DURATION + ' Bulan';
            document.getElementById('summaryPlanMeta').textContent = meta;
            document.getElementById('originalPriceDisplay').textContent = fmt(PLAN_AMOUNT);
            document.getElementById('finalPriceDisplay').textContent = fmt(PLAN_AMOUNT);

            setStep(1);
            fetchPaymentMethods();
        });

        // ====================================
        // Step indicator
        // ====================================
        function setStep(step) {
            for (let i = 1; i <= 3; i++) {
                document.getElementById(`dot-${i}`).classList.toggle('active', i === step);
            }
        }

        // ====================================
        // Fetch payment methods
        // ====================================
        async function fetchPaymentMethods() {
            try {
                const res = await fetch('/api/payment-methods?type=order');
                const json = await res.json();
                document.getElementById('methodsSkeleton').classList.add('hidden');
                if (json.status === 'success' && json.data.is_enabled && json.data.payment_methods?.length) {
                    PAYMENT_METHODS = json.data.payment_methods;
                    renderMethods();
                } else {
                    document.getElementById('methodsEmpty').classList.remove('hidden');
                }
            } catch (e) {
                console.error(e);
                document.getElementById('methodsSkeleton').classList.add('hidden');
                document.getElementById('methodsEmpty').classList.remove('hidden');
            }
        }

        // ====================================
        // Render method cards
        // ====================================
        function getMethodIcon(name) {
            const n = name.toLowerCase();
            if (n.includes('qris')) return { icon: 'fa-qrcode', color: '#7c3aed' };
            if (n.includes('bca')) return { icon: 'fa-university', color: '#005baa' };
            if (n.includes('bri')) return { icon: 'fa-university', color: '#00529c' };
            if (n.includes('bni')) return { icon: 'fa-university', color: '#f97316' };
            if (n.includes('mandiri')) return { icon: 'fa-university', color: '#003d7c' };
            if (n.includes('gopay')) return { icon: 'fa-mobile-alt', color: '#00aed6' };
            if (n.includes('ovo')) return { icon: 'fa-mobile-alt', color: '#4c3494' };
            if (n.includes('dana')) return { icon: 'fa-mobile-alt', color: '#118fcc' };
            if (n.includes('shopeepay')) return { icon: 'fa-mobile-alt', color: '#ee4d2d' };
            if (n.includes('transfer')) return { icon: 'fa-exchange-alt', color: '#ff7900' };
            return { icon: 'fa-wallet', color: '#ff7900' };
        }

        function renderMethods() {
            const list = document.getElementById('methodsList');
            const details = document.getElementById('detailPanels');
            list.innerHTML = '';
            details.innerHTML = '';
            list.classList.remove('hidden');

            PAYMENT_METHODS.forEach((method, i) => {
                const { icon, color } = getMethodIcon(method.name);

                // Method card (left column)
                const card = document.createElement('div');
                card.className = 'method-card rounded-xl p-4 flex items-center gap-3';
                card.dataset.index = i;
                card.onclick = () => selectMethod(i);
                card.innerHTML = `
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: ${color}22; border: 1.5px solid ${color}44;">
                        <i class="fas ${icon} text-sm" style="color: ${color};"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-semibold truncate">${method.name}</p>
                        <p class="text-gray-500 text-xs mt-0.5">${method.account_number ? method.account_number : (method.qris_image_path ? 'Scan QRIS' : 'Lihat detail')}</p>
                    </div>
                    <div class="method-radio"></div>
                `;
                list.appendChild(card);

                // Detail panel (right column)
                const qrisUrl = method.qris_image_path ? `/storage/${method.qris_image_path}` : null;
                const panel = document.createElement('div');
                panel.className = 'detail-panel';
                panel.id = `detail-${i}`;

                panel.innerHTML = `
                    <!-- Method Header -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg" style="background: ${color}22; border: 1.5px solid ${color}44;">
                            <i class="fas ${icon} text-xl" style="color: ${color};"></i>
                        </div>
                        <div>
                            <h2 class="text-white font-bold text-lg leading-tight">${method.name}</h2>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                <span class="text-gray-400 text-xs">Aktif &amp; Tersedia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amount reminder -->
                    <div class="flex items-center justify-between bg-white/3 rounded-xl px-4 py-3 mb-5 border border-white/6">
                        <span class="text-gray-400 text-sm">Jumlah Transfer</span>
                        <span class="text-[#ff7900] font-bold text-lg" id="amountInPanel-${i}">${fmt(FINAL_AMOUNT)}</span>
                    </div>

                    ${method.account_number ? `
                    <!-- Account Number -->
                    <div class="account-copy-card rounded-xl p-4 mb-5" onclick="copyText('${method.account_number}', 'Nomor rekening disalin!')">
                        <p class="text-gray-500 text-xs font-semibold tracking-wider uppercase mb-2">Nomor Rekening / VA</p>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white font-mono font-bold text-2xl tracking-widest">${method.account_number}</span>
                            <div class="w-9 h-9 rounded-lg bg-[#ff7900]/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-copy text-[#ff7900] text-sm"></i>
                            </div>
                        </div>
                        ${method.account_name ? `<p class="text-gray-400 text-xs mt-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#ff7900] inline-block"></span>${method.account_name}</p>` : `<p class="text-gray-400 text-xs mt-2 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#ff7900] inline-block"></span>CV KomikTap</p>`}
                        <p class="text-gray-600 text-xs mt-2 flex items-center gap-1"><i class="fas fa-hand-pointer text-[10px]"></i> Ketuk untuk menyalin</p>
                    </div>
                    ` : ''}

                    ${qrisUrl ? `
                    <!-- QRIS Image -->
                    <div class="mb-5">
                        <p class="text-gray-500 text-xs font-semibold tracking-wider uppercase mb-3">Kode QRIS</p>
                        <div class="flex justify-center">
                            <div class="bg-white p-3 rounded-2xl shadow-xl cursor-pointer hover:scale-105 transition-transform inline-block" onclick="openImageZoom('${qrisUrl}')" title="Ketuk untuk memperbesar">
                                <img src="${qrisUrl}" alt="QRIS ${method.name}" class="w-44 h-44 object-contain rounded-lg">
                            </div>
                        </div>
                        <p class="text-center text-gray-600 text-xs mt-2"><i class="fas fa-search-plus mr-1"></i> Ketuk gambar untuk memperbesar</p>
                    </div>
                    ` : ''}

                    ${method.instructions ? `
                    <!-- Instructions CTA -->
                    <button onclick="openInstructions(${i})" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-[#ff7900]/8 border border-[#ff7900]/20 hover:bg-[#ff7900]/15 hover:border-[#ff7900]/40 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#ff7900]/10 flex items-center justify-center">
                                <i class="fas fa-list-check text-[#ff7900] text-sm"></i>
                            </div>
                            <span class="text-[#ff7900] text-sm font-medium">Lihat Cara Pembayaran</span>
                        </div>
                        <i class="fas fa-chevron-right text-[#ff7900]/60 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </button>
                    ` : ''}

                    <div id="inst-${i}" class="hidden">${method.instructions || ''}</div>
                `;
                details.appendChild(panel);
            });
        }

        // ====================================
        // Select payment method
        // ====================================
        function selectMethod(index) {
            SELECTED_INDEX = index;
            setStep(2);

            document.querySelectorAll('.method-card').forEach(card => {
                card.classList.toggle('active', parseInt(card.dataset.index) === index);
            });

            document.getElementById('placeholderState').classList.add('hidden');
            document.querySelectorAll('.detail-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`detail-${index}`)?.classList.add('active');
            document.getElementById('confirmationForm').classList.remove('hidden');

            // Refresh amount in panel in case voucher was applied
            updatePanelAmounts();

            // Scroll on mobile
            if (window.innerWidth < 1024) {
                const rightCol = document.querySelector('.lg\\:col-span-3 > div');
                if (rightCol) setTimeout(() => rightCol.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
            }
        }

        // ====================================
        // Voucher
        // ====================================
        async function checkVoucher() {
            const codeInput = document.getElementById('voucherInput');
            const msg = document.getElementById('voucherMessage');
            const btn = document.getElementById('btnCheckVoucher');
            const code = codeInput.value.trim();
            if (!code) return;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            msg.classList.add('hidden');

            try {
                const res = await fetch('/api/check-voucher', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ voucher_code: code, amount: PLAN_AMOUNT })
                });
                const json = await res.json();

                msg.classList.remove('hidden');
                if (res.ok && json.status === 'success') {
                    VOUCHER_CODE = code;
                    FINAL_AMOUNT = json.data.final_amount;
                    msg.innerHTML = `<span class="text-green-400"><i class="fas fa-check-circle"></i> ${json.data.message}</span>`;
                    updateSummary(PLAN_AMOUNT, json.data.discount_amount, FINAL_AMOUNT, code);
                } else {
                    VOUCHER_CODE = '';
                    FINAL_AMOUNT = PLAN_AMOUNT;
                    const errMsg = json.data?.message || json.message || 'Kode tidak valid';
                    msg.innerHTML = `<span class="text-red-400"><i class="fas fa-times-circle"></i> ${errMsg}</span>`;
                    updateSummary(PLAN_AMOUNT, 0, PLAN_AMOUNT, null);
                }
                updatePanelAmounts();
            } catch (e) {
                console.error(e);
                msg.classList.remove('hidden');
                msg.innerHTML = '<span class="text-red-400">Terjadi kesalahan sistem.</span>';
            } finally {
                btn.innerHTML = 'Terapkan';
                btn.disabled = false;
            }
        }

        function updateSummary(original, discount, final, code) {
            document.getElementById('originalPriceDisplay').textContent = fmt(original);
            document.getElementById('finalPriceDisplay').textContent = fmt(final);

            const discountRow = document.getElementById('discountRow');
            if (discount > 0 && code) {
                discountRow.classList.remove('hidden');
                document.getElementById('discountAmountDisplay').textContent = '-' + fmt(discount);
                document.getElementById('voucherCodeBadge').textContent = code;
            } else {
                discountRow.classList.add('hidden');
            }
        }

        function updatePanelAmounts() {
            document.querySelectorAll('[id^="amountInPanel-"]').forEach(el => {
                el.textContent = fmt(FINAL_AMOUNT);
            });
        }

        // ====================================
        // Instructions drawer
        // ====================================
        function openInstructions(index) {
            const data = document.getElementById(`inst-${index}`)?.innerHTML || '';
            document.getElementById('instructionsBody').innerHTML = data || '<p class="text-gray-500">Tidak ada instruksi tersedia.</p>';
            const drawer = document.getElementById('instructionsDrawer');
            const content = document.getElementById('instructionsDrawerContent');
            drawer.classList.remove('hidden');
            requestAnimationFrame(() => { content.style.transform = 'translateY(0)'; });
        }

        function closeInstructions() {
            const content = document.getElementById('instructionsDrawerContent');
            content.style.transform = 'translateY(100%)';
            setTimeout(() => document.getElementById('instructionsDrawer').classList.add('hidden'), 350);
        }

        // ====================================
        // Image zoom
        // ====================================
        function openImageZoom(url) {
            document.getElementById('zoomedImage').src = url;
            document.getElementById('imageZoomOverlay').classList.add('show');
        }

        function closeImageZoom() {
            document.getElementById('imageZoomOverlay').classList.remove('show');
        }

        // ====================================
        // Copy
        // ====================================
        function copyText(text, message) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => showToast(message || 'Disalin!'));
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed'; ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast(message || 'Disalin!');
            }
        }

        // ====================================
        // Toast
        // ====================================
        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ====================================
        // Submit Order
        // ====================================
        async function submitOrder() {
            const proof = document.getElementById('proofInput').value.trim();
            const wa = document.getElementById('waInput').value.trim() || '-';
            const btn = document.getElementById('submitBtn');

            if (SELECTED_INDEX < 0) {
                showToast('Pilih metode pembayaran terlebih dahulu!');
                return;
            }

            if (!proof || proof.length < 3) {
                showToast('Masukkan minimal 3 digit referensi transfer!');
                document.getElementById('proofInput').focus();
                return;
            }

            const method = PAYMENT_METHODS[SELECTED_INDEX];
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            setStep(3);

            try {
                const res = await fetch('/api/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        plan_name: PLAN_NAME,
                        device_quota: PLAN_DEVICES || 1,
                        duration_months: PLAN_DURATION || 1,
                        amount: FINAL_AMOUNT,
                        customer_contact: wa,
                        proof_digits: proof,
                        payment_method: method?.name || '',
                        voucher_code: VOUCHER_CODE || ''
                    })
                });

                const json = await res.json();

                if (json.status === 'success') {
                    window.location.href = '/success/' + json.data.transaction_code;
                } else {
                    showToast('Gagal: ' + (json.data?.message || 'Terjadi kesalahan.'));
                    btn.disabled = false;
                    btn.innerHTML = 'Konfirmasi';
                    setStep(2);
                }
            } catch (e) {
                console.error(e);
                showToast('Gagal mengirim data, coba lagi.');
                btn.disabled = false;
                btn.innerHTML = 'Konfirmasi';
                setStep(2);
            }
        }

        // ESC closes overlays
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeImageZoom(); closeInstructions(); }
        });
    </script>
</body>

</html>