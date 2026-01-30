<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - KomikTap</title>
    <link rel="icon" href="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
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
                            text: '#a0a0a0'
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
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="w-full bg-[#0f0e13]/80 backdrop-blur-xl border-b border-white/5 h-20 flex items-center">
        <div class="max-w-7xl mx-auto px-4 w-full flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2 hover:scale-105 transition-transform">
                <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png"
                    alt="KomikTap Logo" class="w-10 h-10">
                <span class="text-white font-bold text-xl italic">KOMIK<span
                        class="text-komik-primary">TAP</span></span>
            </a>
            <div class="hidden md:flex items-center space-x-8">
                <a href="/" class="text-gray-300 hover:text-white font-medium text-sm flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="flex-grow flex items-center justify-center py-20 px-4">
        <div class="max-w-4xl w-full grid md:grid-cols-2 gap-12">

            <!-- Contact Info -->
            <div>
                <h1 class="text-4xl font-bold text-white mb-6">Hubungi Kami</h1>
                <p class="text-komik-text mb-8 leading-relaxed">
                    Punya pertanyaan, kritik, saran, atau kendala pembayaran?
                    Tim support kami siap membantu Anda.
                </p>

                @inject('contact', 'App\Settings\ContactSettings')
                @php
                    // Clean WA number for link (remove spaces, dashes, plus)
                    $waLink = preg_replace('/[^0-9]/', '', $contact->whatsapp_number);

                    // Helper: Markdown Parser (PHP Version matching the JS logic)
                    $parseDesc = function ($desc) {
                        if (!$desc)
                            return '';
                        $lines = explode("\n", $desc);
                        $html = '<ul class="list-none space-y-1">'; // Use list-none by default but enable bullets if lines start with hyphen? No, user wants implicit list.
                        // Actually, let's use list-none and maybe custom checkmark if we want to mimic "The best untuk kamu"
                        // But "3 Devices Login" implies a feature list.
                        // The user's welcome page implementation was simple <ul><li>.
                        // Let's stick to simple implementation but ensuring it renders blocks.

                        // Re-evaluating styling: In welcome page, it might be using default list style or checking parent CSS.
                        // To be safe and modern, let's use a simple list.
                        $html = '<ul class="list-disc pl-4 space-y-1">';

                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (!$trimmed)
                                continue;
                            if (str_starts_with($trimmed, '-')) {
                                $trimmed = trim(substr($trimmed, 1));
                            }
                            // Bold
                            $trimmed = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-white">$1</strong>', $trimmed);
                            // Italic
                            $trimmed = preg_replace('/(\*|_)(.*?)\1/', '<em>$2</em>', $trimmed);

                            $html .= "<li>$trimmed</li>";
                        }
                        $html .= '</ul>';
                        return $html;
                    };
                @endphp

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">WhatsApp</h3>
                            <div class="text-sm text-gray-500 mb-1 leading-relaxed">
                                {!! $parseDesc($contact->whatsapp_description) !!}
                            </div>
                            <a href="https://wa.me/{{ $waLink }}" target="_blank"
                                class="text-komik-primary hover:text-white transition-colors font-medium">
                                {{ $contact->whatsapp_number }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Email</h3>
                            <div class="text-sm text-gray-500 mb-1 leading-relaxed">
                                {!! $parseDesc($contact->email_description) !!}
                            </div>
                            <a href="mailto:{{ $contact->email_address }}"
                                class="text-komik-primary hover:text-white transition-colors font-medium">
                                {{ $contact->email_address }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-komik-primary/10 flex items-center justify-center text-komik-primary text-xl flex-shrink-0">
                            <i class="fa-brands fa-discord"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Discord Community</h3>
                            <div class="text-sm text-gray-500 mb-1 leading-relaxed">
                                {!! $parseDesc($contact->discord_description) !!}
                            </div>
                            <a href="{{ $contact->discord_url }}" target="_blank"
                                class="text-komik-primary hover:text-white transition-colors font-medium">
                                {{ $contact->discord_name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-[#1e1d25] p-8 rounded-2xl border border-white/5 shadow-2xl">
                <h3 class="text-2xl font-bold text-white mb-6">Kirim Pesan</h3>
                <form onsubmit="event.preventDefault(); submitContact();" class="space-y-4">
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama</label>
                        <input id="inputName" type="text"
                            class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors"
                            placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Email</label>
                        <input id="inputEmail" type="email"
                            class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors"
                            placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Pesan</label>
                        <textarea id="inputMessage" rows="4"
                            class="w-full bg-[#0f0e13] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-komik-primary transition-colors"
                            placeholder="Tulis pesan Anda disini..."></textarea>
                    </div>
                    <button id="btnSubmit" type="submit"
                        class="w-full bg-gradient-to-r from-komik-primary to-[#ff5e00] text-white font-bold py-3 rounded-lg hover:shadow-[0_0_20px_rgba(255,121,0,0.3)] transition-all transform hover:-translate-y-1">
                        Kirim Pesan
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 text-center text-sm text-gray-600">
        &copy; {{ date('Y') }} KomikTap. All rights reserved.
    </footer>

    <!-- Notification Toast -->
    <div id="toast"
        class="fixed bottom-4 right-4 bg-komik-card border border-white/10 px-6 py-4 rounded-xl shadow-2xl transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 z-50">
        <i class="fas fa-check-circle text-green-500 text-xl"></i>
        <div>
            <h4 class="text-white font-bold text-sm">Berhasil!</h4>
            <p id="toastMessage" class="text-gray-400 text-xs">Pesan Anda terkirim.</p>
        </div>
    </div>

    <script>
        async function submitContact() {
            const btn = document.getElementById('btnSubmit');
            const originalContent = btn.innerHTML;

            // Collect data
            const name = document.getElementById('inputName').value;
            const email = document.getElementById('inputEmail').value;
            const message = document.getElementById('inputMessage').value;

            // Simple validation
            if (!name || !email || !message) {
                alert('Mohon lengkapi semua field!');
                return;
            }

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';

            try {
                const response = await fetch("{{ route('contact.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name, email, message })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success
                    showToast(data.message);
                    // Reset form
                    document.getElementById('inputName').value = '';
                    document.getElementById('inputEmail').value = '';
                    document.getElementById('inputMessage').value = '';
                } else {
                    alert('Error: ' + (data.message || 'Something went wrong'));
                }

            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').innerText = msg;
            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3000);
        }
    </script>
</body>

</html>