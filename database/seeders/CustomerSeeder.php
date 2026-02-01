<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\License;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sultan User (High Spender)
        $sultan = Customer::firstOrCreate(
            ['contact' => '081122334455'],
            ['name' => 'Budi Sultan', 'notes' => 'Customer VIP, sering beli paket tahunan.']
        );
        $this->createTransaction($sultan, 'Sultan 12 Bulan', 1500000, 'approved');
        $this->createTransaction($sultan, 'Sultan 6 Bulan', 800000, 'approved');

        // 2. Regular User
        $ani = Customer::firstOrCreate(
            ['contact' => 'ani@example.com'],
            ['name' => 'Ani Lestari', 'notes' => 'Tanya-tanya terus tapi beli.']
        );
        $this->createTransaction($ani, 'Starter 1 Bulan', 50000, 'approved');
        $this->createTransaction($ani, 'Starter 3 Bulan', 140000, 'pending');

        // 3. Ketengan User
        $ujang = Customer::firstOrCreate(
            ['contact' => '089988776655'],
            ['name' => 'Ujang Ketengan', 'notes' => 'Suka beli eceran.']
        );
        for ($i=0; $i<5; $i++) {
             $this->createTransaction($ujang, 'Ketengan', 15000, 'approved');
        }

        // 4. Banned User
        $deni = Customer::firstOrCreate(
            ['contact' => '086666666666'],
            ['name' => 'Deni Fraudster', 'notes' => 'Terdeteksi sharing account.', 'is_banned' => true]
        );
        $this->createTransaction($deni, 'Premium 1 Bulan', 100000, 'rejected');

        // 5. New User
        Customer::firstOrCreate(
            ['contact' => '087712312312'],
            ['name' => 'Siti Baru', 'notes' => 'Baru daftar, belum transaksi.']
        );
    }

    private function createTransaction($customer, $plan, $amount, $status)
    {
        Transaction::create([
            'customer_contact' => $customer->contact, // Will auto-sync to customer_id via Model Event
            'plan_name' => $plan,
            'amount' => $amount,
            'device_quota' => 2,
            'duration_months' => 1,
            'proof_digits' => '12345',
            'status' => $status,
            'customer_id' => $customer->id, // Force set just in case
        ]);
    }
}
