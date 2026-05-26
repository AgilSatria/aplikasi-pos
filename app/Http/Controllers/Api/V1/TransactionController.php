<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\GetTransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\TransactionResource;

use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TransactionController extends Controller implements HasMiddleware
{


    public static function middleware()
    {
        return [
            new Middleware(
                PermissionMiddleware::using('view_transactions'),
                only: ['index', 'show']
            ),

            new Middleware(
                PermissionMiddleware::using('create_transactions'),
                only: ['store']
            ),
        ];
    }



    /**
     * Display a listing of the resource.
     */
    public function index(GetTransactionsRequest $request)
    {
        $transactions = Transaction::with(['customer', 'items.product'])->search($request->search)->latest()
            ->paginate($request->input('limit', 15));

        return ApiResponse::success(
            new PaginatedResource($transactions, TransactionResource::class),
            'Transactions retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->input('items');
            $subtotal = 0;
            $transactionItems = [];

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    DB::rollBack();
                    return ApiResponse::error(
                        "Product with ID {$item['product_id']} not found.",
                        Response::HTTP_NOT_FOUND
                    );
                }

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return ApiResponse::error(
                        "Insufficient stock for product '{$product->name}'. Available: {$product->stock}, Requested: {$item['quantity']}.",
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $transactionItems[] = [
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            $transaction = Transaction::create([
                'code' => 'TRX-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                'customer_id' => $request->input('customer_id'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $transaction->items()->createMany($transactionItems);

            DB::commit();

            return ApiResponse::success(
                new TransactionResource($transaction->load(['customer', 'items.product'])),
                'Transaction created successfully.',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error(
                'Failed to create transaction: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'items.product'])->find($id);

        if (!$transaction) {
            return ApiResponse::error(
                'Transaction not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction retrieved successfully.'
        );
    }
}
