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
                    <td class="text-right p-3 text-gray-700">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
                <!-- Total -->
                <tr>
                    <td class="p-3 text-right font-bold text-gray-800">Total</td>
                    <td class="p-3 text-right font-bold text-gray-800">IDR {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="text-center text-sm text-gray-500 mt-8 border-t pt-4">
            <p>Thank you for your business!</p>
            <p>Status: <span class="uppercase font-bold {{ $transaction->status === 'approved' ? 'text-green-600' : ($transaction->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">{{ $transaction->status }}</span></p>
        </div>
    </div>
</body>
</html>
