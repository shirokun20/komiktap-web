<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-8 shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-8 border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">INVOICE</h1>
                <p class="text-gray-600 text-sm">#{{ $transaction->code }}</p>
            </div>
            <div class="text-right">
                <img src="https://komiktap.info/wp-content/uploads/2020/09/cropped-LOGOa-192x192.png" alt="KomikTap Logo" class="h-12 w-auto ml-auto">
                <p class="text-gray-600 text-sm">{{ now()->format('F d, Y') }}</p>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-gray-700 font-bold mb-2">Bill To:</h3>
            <p class="text-gray-600">{{ $transaction->customer_contact }}</p>
        </div>

        @if($transaction->payment_method)
        <div class="mb-8">
            <h3 class="text-gray-700 font-bold mb-2">Payment Method:</h3>
            <p class="text-gray-800 font-medium">{{ $transaction->payment_method }}</p>
            @if($transaction->payment_details)
                @php
                    // Filter out "Notes:" section if present
                    $details = $transaction->payment_details;
                    $parts = explode('Notes:', $details);
                    $cleanDetails = trim($parts[0]);
                @endphp
                <p class="text-gray-500 text-sm whitespace-pre-line">{{ $cleanDetails }}</p>
            @endif
        </div>
        @endif

        <table class="w-full mb-8">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="text-left p-3 font-semibold text-gray-700">Description</th>
                    <th class="text-right p-3 font-semibold text-gray-700">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="p-3 text-gray-700">
                        {{ $transaction->plan_name }}<br>
                        <span class="text-xs text-gray-500">{{ $transaction->device_quota }} Device(s), {{ $transaction->duration_months }} Months</span>
                    </td>
                    <td class="text-right p-3 text-gray-700">
                        @php
                            $subtotal = $transaction->amount + ($transaction->discount_amount ?? 0);
                        @endphp
                        IDR {{ number_format($subtotal, 0, ',', '.') }}
                    </td>
                </tr>

                @if($transaction->discount_amount > 0)
                <tr class="border-b bg-green-50/50">
                    <td class="p-3 text-green-700">
                        Discount ({{ $transaction->voucher_code }})
                    </td>
                    <td class="text-right p-3 text-green-700">
                        - IDR {{ number_format($transaction->discount_amount, 0, ',', '.') }}
                    </td>
                </tr>
                @endif

                <!-- Total -->
                <tr>
                    <td class="p-3 text-right font-bold text-gray-800">Total Paid</td>
                    <td class="p-3 text-right font-bold text-gray-800">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($transaction->status === 'approved' && $transaction->license)
        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-dashed border-gray-300 relative group">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">License Activation Key</h3>
            
            <div class="flex items-center gap-3">
                <code class="text-2xl font-mono font-bold text-gray-800 tracking-wider select-all" id="licenseKey">{{ $transaction->license->key }}</code>
                
                <button onclick="copyKey()" class="text-gray-400 hover:text-gray-800 transition-colors p-2 print:hidden" title="Copy to clipboard">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
            
            <div id="copyFeedback" class="absolute top-4 right-4 text-xs font-medium text-gray-500 opacity-0 transition-opacity duration-300">
                Copied!
            </div>

            <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-200">
                * Use this key to activate your account in the KomikTap app.
            </p>
        </div>

        <script>
            function copyKey() {
                const key = document.getElementById('licenseKey').innerText;
                navigator.clipboard.writeText(key).then(() => {
                    // Show feedback
                    const feedback = document.getElementById('copyFeedback');
                    feedback.classList.remove('opacity-0');
                    setTimeout(() => {
                        feedback.classList.add('opacity-0');
                    }, 2000);
                });
            }
        </script>
        @endif

        <div class="text-center text-sm text-gray-500 mt-8 border-t pt-4">
            <p>Thank you for your business!</p>
            <p>Status: <span class="uppercase font-bold {{ $transaction->status === 'approved' ? 'text-green-600' : ($transaction->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">{{ $transaction->status }}</span></p>
            
            <button onclick="window.print()" class="mt-6 bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg font-bold transition-all print:hidden flex items-center gap-2 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2m-8 2h8a2 2 0 012 2v4a2 2 0 00-2 2h-6a2 2 0 00-2-2v-9a2 2 0 00-2-2h-3m-4 0H5a2 2 0 00-2 2v4a2 2 0 01-2 2" />
                </svg>
                Cetak Invoice
            </button>
        </div>
    </div>
</body>
</html>
