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

        .btn-primary:active {
            transform: scale(0.98);
        }

        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% {
                background-position: 1rem 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        .payment-detail-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body class="antialiased font-sans selection:bg-komik-primary selection:text-white">

    <!-- Navbar -->
    <nav
        class="fixed w-full z-50 bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 transition-all duration-300 group hover:bg-[#0f0e13]/95">
        <div
            class="absolute bottom-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-komik-primary/50 to-transparent opacity-50 group-hover:opacity-100 transition-opacity">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/"
                    class="flex items-center gap-2 group/logo hover:scale-105 transition-transform duration-300">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png"
                        alt="KomikTap Logo" class="w-10 h-10 md:w-12 md:h-12 drop-shadow-[0_0_8px_rgba(255,121,0,0.5)]">
                    <div class="flex flex-col">
                        <span class="text-xl md:text-2xl font-black text-white leading-none tracking-tight">KOMIK<span
                                class="text-komik-primary">TAP</span></span>
                        <span
                            class="text-[0.6rem] md:text-[0.65rem] text-gray-400 font-medium tracking-widest uppercase">Peduli
                            Sesama</span>
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
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-[500px] bg-komik-primary/10 rounded-full blur-[120px] -z-10">
        </div>
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
                            <div class="absolute inset-0 bg-gradient-to-t from-[#1e1d25] to-transparent opacity-80">
                            </div>
                        </div>
                        @endif

                        <div class="p-8">
                            <h1 class="text-3xl font-bold text-white mb-6">{{ $campaign->title }}</h1>

                            <!-- Progress Bar (Visible on Mobile here, duplicated for layout) -->
                            <div class="lg:hidden mb-8">
                                <div class="flex justify-between items-end mb-2">
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Terkumpul</p>
                                        <h3 class="text-xl font-bold text-white">IDR {{ number_format($collected, 0,
                                            ',', '.') }}</h3>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400 mb-1">Target</p>
                                        <h3 class="text-lg font-bold text-gray-300">IDR {{
                                            number_format($campaign->target_amount, 0, ',', '.') }}</h3>
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
                                    <h3 class="text-xl font-bold text-white">IDR {{ number_format($collected, 0, ',',
                                        '.') }}</h3>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700/50 rounded-full h-3 overflow-hidden relative mb-2">
                                <div class="bg-gradient-to-r from-komik-primary to-komik-accent h-full rounded-full progress-bar-striped relative"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="text-right text-xs text-gray-400">
                                dari target <span class="text-white font-bold">IDR {{
                                    number_format($campaign->target_amount, 0, ',', '.') }}</span>
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
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">IDR</span>
                                <input type="number" id="customAmount" min="1000"
                                    class="w-full bg-black/20 border border-white/10 rounded-xl py-3 pl-12 pr-4 text-white font-bold text-lg focus:outline-none focus:border-komik-primary/50 transition-colors"
                                    placeholder="0">
                            </div>
                        </div>

                        <button onclick="processDonation()"
                            class="w-full py-4 rounded-xl btn-primary text-white font-bold text-lg shadow-lg shadow-komik-primary/25 hover:shadow-komik-primary/50 transition-all flex items-center justify-center gap-2">
                            <span>Lanjut Pembayaran</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- QRIS Modal -->
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

                <div class="text-center mb-6">
                    <p class="text-gray-400 text-sm">Total Donasi</p>
                    <p class="text-komik-primary font-bold text-2xl" id="modalAmountDisplay">IDR 0</p>
                </div>

                <!-- Payment Methods -->
                <div class="flex flex-wrap gap-2 justify-center mb-6" id="paymentMethodsContainer">
                    <div class="w-full text-center py-4">
                        <i class="fas fa-circle-notch fa-spin text-komik-primary"></i>
                    </div>
                </div>

                <div id="paymentDetailsContainer"></div>

                <!-- Confirmation -->
                <div class="bg-white/5 rounded-xl p-4 border border-white/10 text-left mt-6">
                    <label class="block text-gray-400 text-xs mb-2">Konfirmasi Donatur</label>
                    <input type="text" id="waInput" placeholder="Nomor WA / Email (Opsional)"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600 mb-3">

                    <div class="flex gap-2">
                        <input type="text" id="proofInput" placeholder="3-5 Digit Terakhir Bukti Transfer"
                            class="flex-1 bg-black/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-komik-primary/50 placeholder-gray-600">
                        <button id="submitBtn" onclick="submitDonation()"
                            class="bg-komik-primary hover:bg-komik-primaryHover text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Kirim
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-2 italic">*Masukkan digit terakhir nomor referensi/struk
                        untuk verifikasi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Modal -->
    <div id="instructionsModal" class="fixed inset-0 z-[70] hidden opacity-0 transition-opacity duration-300 text-left">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeInstructionsModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
            <div class="glass-card bg-[#1a1a1a] border border-white/10 rounded-3xl p-6 relative shadow-2xl scale-95 transition-transform duration-300 transform"
                id="instructionsModalContent">
                <button onclick="closeInstructionsModal()"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                <h3 class="text-lg font-bold text-white mb-4">Petunjuk Pembayaran</h3>
                <div class="text-gray-300 text-sm payment-instructions max-h-[60vh] overflow-y-auto"
                    id="instructionsContentBody">
                    <!-- Content populated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div id="imageZoomModal" class="fixed inset-0 z-[80] hidden opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-sm" onclick="closeImageZoom()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl p-4">
            <button onclick="closeImageZoom()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="flex items-center justify-center">
                <img id="zoomedImage" src="" alt="QRIS Code" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
            </div>
        </div>
    </div>

    <script>
        let SELECTED_AMOUNT = 0;
        let CAMPAIGN_TITLE = '{{ $campaign->title }}';

        function setAmount(amount) {
            document.getElementById('customAmount').value = amount;
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.classList.remove('border-komik-primary', 'bg-komik-primary/10', 'text-white');
                btn.classList.add('border-white/10', 'bg-white/5', 'text-gray-300');
                if (parseInt(btn.dataset.amount) === amount) {
                    btn.classList.add('border-komik-primary', 'bg-komik-primary/10', 'text-white');
                    btn.classList.remove('border-white/10', 'bg-white/5', 'text-gray-300');
                }
            });
        }

        document.getElementById('customAmount').addEventListener('input', (e) => {
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
            openQrisModal();
        }

        function openQrisModal() {
            const modal = document.getElementById('qrisModal');
            const content = document.getElementById('qrisContent');
            document.getElementById('modalAmountDisplay').textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(SELECTED_AMOUNT);

            modal.classList.remove('hidden');
            // Trigger reflow
            void modal.offsetWidth;

            modal.classList.remove('opacity-0');

            setTimeout(() => {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);

            if (PAYMENT_METHODS.length === 0) fetchPaymentMethods();
        }

        function closeQrisModal() {
            const modal = document.getElementById('qrisModal');
            const content = document.getElementById('qrisContent');

            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');

            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        function openInstructionsModal(index) {
            const content = document.getElementById(`instructions-data-${index}`).innerHTML;
            const modal = document.getElementById('instructionsModal');
            const body = document.getElementById('instructionsContentBody');

            body.innerHTML = content;
            modal.classList.remove('hidden');

            // Trigger reflow
            void modal.offsetWidth;

            modal.classList.remove('opacity-0');
            setTimeout(() => {
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

        function openImageZoom(imageUrl) {
            const modal = document.getElementById('imageZoomModal');
            const img = document.getElementById('zoomedImage');
            img.src = imageUrl;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
        }

        function closeImageZoom() {
            const modal = document.getElementById('imageZoomModal');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        let PAYMENT_METHODS = [];
        let SELECTED_PAYMENT_INDEX = 0;

        async function fetchPaymentMethods() {
            try {
                // Fetch specifically for donation
                const response = await fetch('/api/payment-methods?type=donation');
                const result = await response.json();
                if (result.status === 'success' && result.data.is_enabled) {
                    PAYMENT_METHODS = result.data.payment_methods || [];
                    renderPaymentMethods();
                }
            } catch (error) { console.error(error); }
        }

        function renderPaymentMethods() {
            const tabsContainer = document.getElementById('paymentMethodsContainer');
            const detailsContainer = document.getElementById('paymentDetailsContainer');

            if (PAYMENT_METHODS.length === 0) {
                tabsContainer.innerHTML = '<div class="text-gray-500 italic">Memuat metode pembayaran...</div>';
                return;
            }

            // Tabs
            tabsContainer.innerHTML = PAYMENT_METHODS.map((method, index) => `
                <button onclick="selectPaymentMethod(${index})" 
                        class="payment-method-btn px-4 py-2 rounded-lg border border-white/10 bg-white/5 text-sm font-medium text-gray-400 hover:text-white hover:border-komik-primary hover:bg-komik-primary/10 transition-all"
                        data-index="${index}">
                    ${method.name}
                </button>
            `).join('');

            // Details
            detailsContainer.innerHTML = PAYMENT_METHODS.map((method, index) => {
                const qrisUrl = method.qris_image_path ? `/storage/${method.qris_image_path}` : null;
                const instructions = method.instructions || '';

                // Determine Icon based on name (Simple heuristic)
                let iconClass = 'fa-wallet';
                if (method.name.toLowerCase().includes('qris')) iconClass = 'fa-qrcode';
                else if (method.name.toLowerCase().includes('bca')) iconClass = 'fa-university'; // or specific bank icon

                return `
                <div class="payment-detail-content hidden" id="payment-detail-${index}">
                    <!-- Method Header Card -->
                    <div class="flex items-center gap-4 mb-6 bg-white/5 p-4 rounded-2xl border border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-komik-primary to-orange-600 flex items-center justify-center text-white shadow-lg shadow-komik-primary/20 shrink-0">
                            <i class="fas ${iconClass} text-lg"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-bold text-white leading-tight">${method.name}</h3>
                            <div class="inline-flex items-center gap-1.5 mt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                <p class="text-gray-400 text-xs font-medium modalPlanNameDisplay">Pembayaran Donasi - ${CAMPAIGN_TITLE}</p>
                            </div>
                        </div>
                    </div>
                    
                    ${method.account_number ? `
                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl p-4 mb-4 border border-white/10 relative overflow-hidden group cursor-pointer" onclick="copyToClipboard('${method.account_number}')">
                        <div class="absolute inset-0 bg-komik-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <p class="text-xs text-gray-500 mb-1 font-medium tracking-wide">NOMOR REKENING / VA</p>
                        <div class="text-2xl font-mono font-bold text-white tracking-widest flex items-center justify-between gap-2 group-hover:text-komik-primary transition-colors">
                            <span>${method.account_number}</span>
                            <i class="fas fa-copy text-sm text-gray-600 group-hover:text-komik-primary transition-colors"></i>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 font-medium uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-komik-primary"></span>
                            CV KomikTap
                        </p>
                    </div>
                    ` : ''}

                    ${qrisUrl ? `
                    <div class="p-2 bg-white rounded-xl mx-auto max-w-[200px] mb-6 shadow-lg shadow-white/5 cursor-pointer hover:shadow-komik-primary/20 hover:scale-105 transition-all" onclick="openImageZoom('${qrisUrl}')" title="Klik untuk memperbesar">
                        <img src="${qrisUrl}" alt="QRIS Code" class="w-full h-auto rounded-lg">
                    </div>
                    <p class="text-xs text-center text-gray-500 mb-4"><i class="fas fa-search-plus"></i> Klik gambar untuk memperbesar</p>
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
                </div>`;
            }).join('') + `
                <div id="payment-placeholder" class="text-center py-12 px-6">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center text-gray-600 mx-auto mb-4 border border-white/5">
                        <i class="fas fa-mouse-pointer text-2xl"></i>
                    </div>
                    <h4 class="text-white font-bold mb-2">Pilih Metode Pembayaran</h4>
                    <p class="text-gray-500 text-sm">Silakan pilih salah satu metode di atas untuk melihat detail pembayaran.</p>
                </div>
            `;
        }

        function selectPaymentMethod(index) {
            SELECTED_PAYMENT_INDEX = index; // Track selection

            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active', 'border-komik-primary', 'text-white', 'bg-komik-primary/10');
                btn.classList.add('text-gray-400', 'bg-white/5', 'border-white/10');

                if (parseInt(btn.dataset.index) === index) {
                    btn.classList.add('active', 'border-komik-primary', 'text-white', 'bg-komik-primary/10');
                    btn.classList.remove('text-gray-400', 'bg-white/5', 'border-white/10');
                }
            });

            document.querySelectorAll('.payment-detail-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('payment-placeholder')?.classList.add('hidden');

            const detailEl = document.getElementById(`payment-detail-${index}`);
            if (detailEl) {
                detailEl.classList.remove('hidden');
                // Tiny fade in animation
                detailEl.animate([
                    { opacity: 0, transform: 'translateY(10px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 300, easing: 'ease-out' });
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.activeElement;
                alert('Disalin: ' + text);
            });
        }

        async function submitDonation() {
            const wa = document.getElementById('waInput').value || '-';
            const proof = document.getElementById('proofInput').value;
            const btn = document.getElementById('submitBtn');

            if (!proof) { alert('Mohon sertakan bukti transfer.'); return; }

            // Get selected payment method name
            const selectedMethod = PAYMENT_METHODS[SELECTED_PAYMENT_INDEX];
            const methodName = selectedMethod ? selectedMethod.name : '';

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch('/api/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({
                        plan_name: CAMPAIGN_TITLE,
                        device_quota: 1,
                        duration_months: 1,
                        amount: SELECTED_AMOUNT,
                        customer_contact: wa,
                        proof_digits: proof,
                        payment_method: methodName // Include Payment Method
                    })
                });
                const result = await response.json();
                if (result.status === 'success') { window.location.href = '/success/' + result.data.transaction_code; }
                else { alert('Error: ' + result.data.message); btn.disabled = false; btn.textContent = 'Kirim'; }
            } catch (error) { console.error(error); alert('Gagal mengirim data.'); btn.disabled = false; btn.textContent = 'Kirim'; }
        }
    </script>
</body>

</html>