<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Seed FAQ publik (landing + aplikasi via /api/faqs).
     *
     * Idempotent: cocokkan berdasarkan question, update answer bila berubah,
     * agar tidak menimpa FAQ custom admin yang question-nya berbeda dan tidak
     * membuat duplikat saat dijalankan ulang.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Bagaimana cara aktivasi?',
                'answer' => 'Setelah pembayaran berhasil, PM Admin di WA atau Fanspage FB untuk minta kode Aktivasinya. Masukkan kode tersebut di menu Sidebar > Cek Lisensi > Masukan Lisensi > Klik Check pada aplikasi KomikTap.',
            ],
            [
                'question' => 'Apakah bisa ganti device?',
                'answer' => 'Ya, lisensi mendukung multi-device. dengan catatan harus lapor Admin yang ada pada halaman contact.',
            ],
            [
                'question' => 'Apakah pembayaran aman?',
                'answer' => "Aman. Pembayaran kami sudah otomatis via QRIS — begitu pembayaran berhasil, lisensi langsung diproses oleh sistem tanpa perlu konfirmasi manual. 🙏",
            ],
            [
                'question' => 'Berapa lama proses verifikasi?',
                'answer' => 'Verifikasi pembayaran otomatis dan hanya butuh beberapa saat setelah QRIS terbayar. Jika status belum berubah, tunggu maksimal 5 menit lalu hubungi Admin via halaman contact.',
            ],
            [
                'question' => 'Apakah bisa refund?',
                'answer' => 'Maaf, karena ini adalah produk digital (lisensi key), kami tidak melayani refund setelah key dikirimkan. Pastikan paket yang Anda pilih sudah sesuai. karena sudah banyak fitur baru di app nya',
            ],
        ];

        foreach ($faqs as $sort => $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'is_active' => true,
                    'sort_order' => $sort + 1,
                ]
            );
        }
    }
}
