@inject('contact', 'App\Settings\ContactSettings')
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KomikTap Peduli - Donasi</title>
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
                        <span class="text-xl md:text-2xl font-black text-white leading-none tracking-tight">KOMIK<span
                                class="text-komik-primary">TAP</span></span>
                        <span class="text-[0.6rem] md:text-[0.65rem] text-gray-400 font-medium tracking-widest uppercase">Peduli Sesama</span>
                    </div>
                </a>

                <!-- Back to Home -->
                <a href="/" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium">
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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-komik-primary/10 border border-komik-primary/20 text-komik-primary text-xs font-semibold uppercase tracking-wider mb-6">
                    <i class="fa-solid fa-heart"></i> Program Donasi
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">Bantu Sesama, <span class="text-gradient">Bangun Harapan</span></h1>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto leading-relaxed">
                    Pilih campaign donasi yang ingin Anda dukung. Setiap kontribusi Anda sangat berarti bagi mereka yang membutuhkan.
                </p>
            </div>

            @if($campaigns->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($campaigns as $campaign)
                        <div class="glass-card rounded-3xl overflow-hidden flex flex-col group cursor-pointer hover:border-komik-primary/50 transition-all duration-300" onclick="window.location.href='{{ route('donation.show', $campaign->slug) }}'">
                            <div class="h-48 bg-gray-800 relative overflow-hidden">
                                @if($campaign->image_path)
                                    <img src="{{ Storage::url($campaign->image_path) }}" alt="{{ $campaign->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-white/5">
                                        <i class="fas fa-hand-holding-heart text-4xl text-gray-600"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1e1d25] to-transparent opacity-80"></div>
                            </div>
                            
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold text-white mb-3 group-hover:text-komik-primary transition-colors">{{ $campaign->title }}</h3>
                                <p class="text-gray-400 text-sm mb-6 line-clamp-3 flex-1">{{ \Illuminate\Support\Str::limit($campaign->description, 100) }}</p>
                                
                                <div class="mt-auto">
                                    <div class="flex justify-between text-xs text-gray-400 mb-2">
                                        <span>Target: IDR {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <button class="w-full py-3 rounded-xl border border-white/10 bg-white/5 text-white hover:bg-komik-primary hover:border-komik-primary hover:text-white transition-all font-medium text-sm">
                                        Donasi Sekarang
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-box-open text-4xl text-gray-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Belum Ada Campaign</h3>
                    <p class="text-gray-500">Saat ini belum ada program donasi yang aktif.</p>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-[#0f0e13] pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-600 text-sm">
                &copy; {{ date('Y') }} KomikTap Peduli. Bagian dari KomikTap.
            </p>
        </div>
    </footer>

</body>
</html>
