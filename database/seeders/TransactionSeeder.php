<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers
        $customers = [
            Customer::firstOrCreate(['name' => 'Budi Santoso', 'phone' => '081234567890']),
            Customer::firstOrCreate(['name' => 'Siti Nurhaliza', 'phone' => '082345678901']),
            Customer::firstOrCreate(['name' => 'Ahmad Fauzi', 'phone' => '083456789012']),
        ];

        // Get existing products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please seed products first.');
            return;
        }

        $taxRate = 0.11; // PPN 11%

        $transactions = [
            [
                'code' => 'TRX-20260519-001',
                'customer' => $customers[0],
                'items' => [
                    ['product_index' => 0, 'quantity' => 2],
                    ['product_index' => 1, 'quantity' => 1],
                ],
            ],
            [
                'code' => 'TRX-20260519-002',
                'customer' => $customers[1],
                'items' => [
                    ['product_index' => 2, 'quantity' => 3],
                ],
            ],
            [
                'code' => 'TRX-20260519-003',
                'customer' => $customers[2],
                'items' => [
                    ['product_index' => 0, 'quantity' => 1],
                    ['product_index' => 2, 'quantity' => 2],
                    ['product_index' => 3 % $products->count(), 'quantity' => 1],
                ],
            ],
            [
                'code' => 'TRX-20260519-004',
                'customer' => $customers[0],
                'items' => [
                    ['product_index' => 1, 'quantity' => 5],
                ],
            ],
            [
                'code' => 'TRX-20260519-005',
                'customer' => $customers[2],
                'items' => [
                    ['product_index' => 0, 'quantity' => 1],
                    ['product_index' => 1, 'quantity' => 2],
                ],
            ],
        ];

        foreach ($transactions as $txData) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($txData['items'] as $item) {
                $product = $products[$item['product_index'] % $products->count()];
                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            $tax = round($subtotal * $taxRate);
            $total = $subtotal + $tax;

            $transaction = Transaction::create([
                'code' => $txData['code'],
                'customer_id' => $txData['customer']->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            foreach ($itemsData as $itemData) {
                TransactionItem::create(array_merge($itemData, [
                    'transaction_id' => $transaction->id,
                ]));
            }
        }
    }
}
