<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    /**
     * Seed halaman legal publik (Kebijakan Privasi & Syarat Layanan).
     *
     * URL publik (domain produksi https://ktapk.org/):
     * - https://ktapk.org/privacy-policy  (Application privacy policy link)
     * - https://ktapk.org/terms            (Application terms of service link)
     *
     * Idempotent: hanya mengisi saat slug belum ada, agar tidak
     * menimpa perubahan yang dibuat admin via panel.
     */
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Kebijakan Privasi',
                'content' => <<<'MARKDOWN'
                    ## 1. Pendahuluan

                    KomikTap ("kami") mengoperasikan platform komik digital di **https://ktapk.org/** beserta aplikasi pendampingnya. Kebijakan Privasi ini menjelaskan data apa yang kami kumpulkan, bagaimana kami menggunakannya, dan hak Anda atas data tersebut.

                    ## 2. Data yang Kami Kumpulkan

                    **a. Data Login Google (OAuth)** — saat Anda masuk dengan akun Google, kami menerima dan menyimpan:
                    - Nama tampilan
                    - Alamat email
                    - Foto profil
                    - ID unik akun Google Anda (untuk mengaitkan sesi login)

                    **b. Data transaksi & lisensi** — kontak pembeli, riwayat pembelian, kode lisensi, dan status pembayaran.

                    **c. Data perangkat** — pengenal perangkat yang Anda daftarkan untuk aktivasi lisensi.

                    **d. Data teknis** — log server standar (alamat IP, jenis perangkat, waktu akses) untuk keamanan dan pencegahan penyalahgunaan.

                    ## 3. Cara Kami Menggunakan Data

                    - Menyediakan dan menjaga sesi login (termasuk login dengan Google)
                    - Menampilkan riwayat pembelian dan status lisensi Anda
                    - Memproses pembayaran, donasi, dan aktivasi perangkat
                    - Mencegah penipuan, spam, dan penyalahgunaan layanan
                    - Mengirim informasi penting terkait akun atau transaksi Anda

                    ## 4. Berbagi Data

                    Kami **tidak menjual** data pribadi Anda. Data hanya dibagikan seperlunya kepada:

                    - **Google** — semata-mata untuk proses autentikasi OAuth yang Anda mulai
                    - **Payment gateway** (Tripay / Fansku) — untuk memproses pembayaran QRIS dan transaksi Anda

                    Kami tidak menggunakan data login Google Anda untuk iklan dan tidak membagikannya ke pihak lain selain yang disebut di atas.

                    ## 5. Penyimpanan & Keamanan

                    Data disimpan di server kami dengan akses terbatas dan langkah pengamanan yang wajar (enkripsi koneksi HTTPS, kontrol akses, pencatatan aktivitas). Data login Google hanya disimpan selama akun Anda aktif dan dihapus saat Anda meminta penghapusan akun.

                    ## 6. Cookie & Sesi

                    Kami menggunakan cookie/sesi teknis yang diperlukan agar login, checkout, dan preferensi dasar dapat berfungsi. Tidak ada cookie pelacakan iklan pihak ketiga.

                    ## 7. Hak Anda

                    Anda berhak meminta akses, perbaikan, atau penghapusan data pribadi Anda kapan saja, termasuk memutuskan kaitan login Google dan menghapus akun. Hubungi kami melalui halaman [Contact](/contact) atau email **support@komiktap.id** — permintaan akan kami proses maksimal 7 hari kerja.

                    ## 8. Perubahan Kebijakan

                    Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini beserta tanggal pembaruannya.

                    ---

                    *Terakhir diperbarui: September 2026. Kontak: [Contact](/contact) — support@komiktap.id*
                    MARKDOWN,
                'is_published' => true,
            ]
        );

        Page::firstOrCreate(
            ['slug' => 'terms'],
            [
                'title' => 'Syarat dan Ketentuan Layanan',
                'content' => <<<'MARKDOWN'
                    ## 1. Layanan

                    KomikTap ("kami") menyediakan platform komik digital di **https://ktapk.org/**, meliputi katalog bacaan, penjualan lisensi akses premium, donasi kreator, dan aplikasi pendamping. Dengan menggunakan layanan ini, Anda setuju terikat pada Syarat dan Ketentuan ini.

                    ## 2. Akun

                    - Sebagian fitur (misalnya riwayat pembelian) memerlukan login, termasuk opsi masuk dengan akun Google.
                    - Anda bertanggung jawab menjaga keamanan akun dan perangkat terdaftar Anda.
                    - Anda wajib memberikan informasi yang benar saat checkout atau aktivasi lisensi.

                    ## 3. Lisensi & Pembayaran

                    - Lisensi bersifat pribadi, terikat pada kuota perangkat dan durasi paket yang Anda beli.
                    - Lisensi aktif setelah pembayaran terverifikasi berstatus disetujui.
                    - Dilarang membagikan, menjual kembali, atau memindahtangankan lisensi tanpa izin tertulis dari kami.
                    - Pelanggaran dapat mengakibatkan penangguhan atau pencabutan lisensi tanpa pengembalian dana.

                    ## 4. Donasi

                    Donasi bersifat sukarela dan tidak dapat dikembalikan, kecuali terbukti terjadi kesalahan sistem atau penagihan ganda yang dapat diverifikasi.

                    ## 5. Hal yang Dilarang

                    - Mengakali sistem lisensi, pembayaran, unduhan, atau batas perangkat
                    - Melakukan scraping, serangan, spam, atau penyalahgunaan teknis lainnya
                    - Mengunggah atau menyebarkan konten yang melanggar hukum atau hak cipta pihak lain
                    - Menyamar sebagai pihak lain atau memberikan informasi palsu

                    ## 6. Ketersediaan & Batasan Tanggung Jawab

                    Kami berupaya menjaga layanan tetap tersedia, tetapi tidak menjamin layanan bebas gangguan 100%. Sejauh diizinkan hukum yang berlaku, kami tidak bertanggung jawab atas kerugian tidak langsung akibat penggunaan atau ketidakmampuan menggunakan layanan.

                    ## 7. Perubahan Syarat

                    Syarat ini dapat diperbarui sewaktu-waktu. Penggunaan layanan secara berkelanjutan setelah perubahan berarti Anda menyetujui versi terbaru.

                    ## 8. Kontak

                    Pertanyaan mengenai syarat ini dapat disampaikan melalui halaman [Contact](/contact) atau email **support@komiktap.id**.

                    ---

                    *Terakhir diperbarui: September 2026.*
                    MARKDOWN,
                'is_published' => true,
            ]
        );
    }
}
