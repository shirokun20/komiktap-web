<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - KomikTap</title>
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
                    },
                    typography: (theme) => ({
                        DEFAULT: {
                            css: {
                                color: theme('colors.gray.400'),
                                h1: { color: theme('colors.white') },
                                h2: { color: theme('colors.white') },
                                h3: { color: theme('colors.white') },
                                strong: { color: theme('colors.white') },
                                a: { color: theme('colors.komik.primary'), '&:hover': { color: theme('colors.komik.primaryHover') } },
                            },
                        },
                    }),
                }
            },
            plugins: [
                function({ addBase, theme }) {
                    addBase({
                        'h1': { fontSize: theme('fontSize.2xl') },
                        'h2': { fontSize: theme('fontSize.xl') },
                        'h3': { fontSize: theme('fontSize.lg') },
                    })
                }
            ]
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
        .prose h1, .prose h2, .prose h3 { margin-bottom: 0.5em; margin-top: 1.5em; font-weight: 700; }
        .prose ul { list-style-type: disc; padding-left: 1.5em; }
        .prose ol { list-style-type: decimal; padding-left: 1.5em; }
        .prose p { margin-bottom: 1em; line-height: 1.7; }
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
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-300 hover:text-white font-medium text-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex justify-center relative px-4 pt-32 pb-20">
        <!-- Background Splatter -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[300px] bg-gradient-to-b from-komik-primary/5 to-transparent -z-10"></div>

        <div class="max-w-4xl w-full">
            <div class="glass-card p-10 rounded-3xl">
                <h1 class="text-4xl font-bold text-white mb-8 border-b border-white/10 pb-4">{{ $page->title }}</h1>
                
                <article class="prose prose-invert max-w-none text-gray-300">
                    {!! str($page->content)->markdown() !!}
                </article>

                <div class="mt-12 text-xs text-gray-600 border-t border-white/5 pt-4">
                    Last updated: {{ $page->updated_at->format('d M Y') }}
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 text-center text-gray-600 text-sm border-t border-white/5 bg-[#0f0e13]/90 backdrop-blur-xl">
        &copy; {{ date('Y') }} KomikTap.
    </footer>

</body>
</html>
