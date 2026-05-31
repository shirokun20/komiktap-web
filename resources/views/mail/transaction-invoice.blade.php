<x-mail::message>
# Invoice #{{ $transaction->code }}

Terima kasih atas pembelian Anda. Berikut detail transaksi Anda:

**Plan:** {{ $transaction->plan_name }}  
**Durasi:** {{ $transaction->duration_months }} Bulan • {{ $transaction->device_quota }} Device  
**Harga Normal:** IDR {{ number_format($transaction->amount + ($transaction->discount_amount ?? 0), 0, ',', '.') }}  
@if($transaction->discount_amount > 0)
**Diskon ({{ $transaction->voucher_code }}):** - IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}  
@endif
**Total Dibayar:** IDR {{ number_format($transaction->amount, 0, ',', '.') }}  
**Status:** {{ ucfirst($transaction->status) }}

@if($transaction->tripay_reference)
**TriPay Reference:** {{ $transaction->tripay_reference }}  
@endif
@if($transaction->tripay_payment_method)
**Metode Pembayaran:** {{ $transaction->tripay_payment_method }}  
@endif

---

@php
    $isDonation = $transaction->plan_name === 'Donasi' ||
        \App\Models\DonationCampaign::where('title', $transaction->plan_name)->exists();
    $licenseKey = $transaction->license?->key ?? null;
@endphp

@if(!$isDonation && $licenseKey)
## 🔑 License Key Anda

<x-mail::panel>
**{{ $licenseKey }}**
</x-mail::panel>

Simpan license key ini dengan baik. Gunakan untuk mengaktifkan aplikasi KomikTap di perangkat Anda.

@endif

<x-mail::button :url="route('invoices.show', $transaction)">
Lihat Invoice
</x-mail::button>

Terima kasih telah menggunakan KomikTap!

Thanks,<br>
![KomikTap](https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png)
</x-mail::message>
