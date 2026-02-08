<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download KomikTap - Premium Comic Reader</title>
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
                            primaryHover: '#ff9100',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f0e13; color: #b8b8b8; font-family: 'Poppins', sans-serif; }
        .glass-card {
            background: rgba(30, 29, 37, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff7900 0%, #ff5e00 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(255, 121, 0, 0.4); }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="/" class="flex items-center gap-2">
                    <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="Logo" class="w-10 h-10">
                    <span class="text-white font-bold text-xl italic tracking-tighter">KOMIK<span class="text-komik-primary">TAP</span></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center relative px-4 pt-20">
        <!-- Background Splatter -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-komik-primary/10 rounded-full blur-[100px] -z-10"></div>

        <div class="max-w-4xl w-full">
            @if($apkVersions->count() > 0)
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-white mb-2">Download KomikTap</h1>
                    <p class="text-gray-400">Pilih versi yang sesuai dengan perangkat Android kamu.</p>
                </div>

                <div class="grid gap-8">
                    @foreach($apkVersions as $apk)
                    <div class="glass-card p-8 rounded-3xl relative overflow-hidden flex flex-col md:flex-row gap-8 items-start hover:border-komik-primary/30 transition-colors">
                        <!-- Left: Info -->
                        <div class="flex-1 text-left">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fab fa-android text-2xl text-green-500"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Version {{ $apk->version_name }}</h2>
                                    <p class="text-xs text-gray-500 font-mono">Build {{ $apk->version_code }} • Updated {{ $apk->updated_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            
                            @if($apk->min_android_version)
                            <div class="inline-block px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold mb-6">
                                Requires {{ $apk->min_android_version }}
                            </div>
                            @endif

                            <div class="bg-black/20 p-5 rounded-xl border border-white/5">
                                <h3 class="text-white text-sm font-semibold mb-2 flex items-center gap-2">
                                    <i class="fas fa-clipboard-list text-komik-primary"></i> What's New
                                </h3>
                                <div class="prose prose-invert prose-xs max-w-none text-gray-400">
                                    {!! str($apk->changelog)->markdown() !!}
                                </div>
                            </div>
                        </div>

                        <!-- Right: Action -->
                        <div class="flex flex-col gap-3 w-full md:w-48 shrink-0 md:border-l md:border-white/5 md:pl-8 justify-center">
                            <a href="{{ route('download.version', $apk->version_code) }}" class="btn-primary py-3 rounded-xl font-bold flex items-center justify-center gap-2 text-sm shadow-lg shadow-komik-primary/20 hover:shadow-komik-primary/40">
                                <i class="fas fa-download"></i>
                                Download
                            </a>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Size</p>
                                <p class="text-sm text-gray-300 font-mono">
                                    @if(Storage::disk('public')->exists($apk->file_path))
                                        {{ number_format(Storage::disk('public')->size($apk->file_path) / 1024 / 1024, 2) }} MB
                                    @else
                                        Unknown
                                    @endif
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Total Downloads</p>
                                <p class="text-sm text-gray-300 font-mono">{{ number_format($apk->download_count) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            @else
                <div class="py-20 text-center glass-card rounded-3xl">
                    <i class="fas fa-box-open text-gray-600 text-6xl mb-4"></i>
                    <h2 class="text-2xl font-bold text-white mb-2">Belum ada versi tersedia</h2>
                    <p class="text-gray-400">Silakan cek kembali nanti untuk update terbaru.</p>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center text-gray-600 text-sm">
        &copy; {{ date('Y') }} KomikTap. app.
    </footer>

</body>
</html>
