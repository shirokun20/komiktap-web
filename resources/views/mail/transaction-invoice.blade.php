<x-mail::message>
# Invoice #{{ $transaction->code }}

Thank you for your purchase. Here are your transaction details:

**Plan:** {{ $transaction->plan_name }}  
**Amount:** IDR {{ number_format($transaction->amount + ($transaction->discount_amount ?? 0), 0, ',', '.') }}  
@if($transaction->discount_amount > 0)
**Discount ({{ $transaction->voucher_code }}):** - IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}  
@endif
**Total:** IDR {{ number_format($transaction->amount, 0, ',', '.') }}  
**Status:** {{ ucfirst($transaction->status) }}

<x-mail::button :url="route('invoices.show', $transaction)">
View Invoice
</x-mail::button>

Thanks,<br>
![KomikTap](https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png)
</x-mail::message>
