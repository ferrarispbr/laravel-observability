<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;
use App\OrderStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with(['items.product'])
            ->findOrFail($order->id);

        return response()->json([
            'data' => $order,
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $order = $request->user()->orders()->create([
                'status' => OrderStatus::PENDENTE,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->price,
                ]);

                $total += $product->price * $itemData['quantity'];
            }

            $order->update([
                'total' => $total,
            ]);

            return $order->load(['items.product']);
        });

        return response()->json([
            'message' => 'Pedido criado com sucesso.',
            'data' => $order,
        ], 201);
    }

    public function confirm(Request $request, Order $order): JsonResponse
    {
        $order = DB::transaction(function () use ($request, $order) {
            $order = $request->user()
                ->orders()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== OrderStatus::PENDENTE) {
                throw ValidationException::withMessages([
                    'order' => ['Somente pedidos pendentes podem ser confirmados.'],
                ]);
            }

            foreach ($order->items as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => ["Estoque insuficiente para o produto {$product->name}."],
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $order->update([
                'status' => OrderStatus::CONFIRMADO,
            ]);

            return $order->load(['items.product']);
        });

        return response()->json([
            'message' => 'Pedido confirmado com sucesso.',
            'data' => $order,
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $order = DB::transaction(function () use ($request, $order) {
            $order = $request->user()
                ->orders()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status === OrderStatus::CANCELADO) {
                throw ValidationException::withMessages([
                    'order' => ['Este pedido já está cancelado.'],
                ]);
            }

            if ($order->status === OrderStatus::CONFIRMADO) {
                foreach ($order->items as $item) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->findOrFail($item->product_id);

                    $product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => OrderStatus::CANCELADO,
            ]);

            return $order->load(['items.product']);
        });

        return response()->json([
            'message' => 'Pedido cancelado com sucesso.',
            'data' => $order,
        ]);
    }

}
