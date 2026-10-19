<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_pending_order(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 10,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Pedido criado com sucesso.')
            ->assertJsonPath('data.status', OrderStatus::PENDENTE->value);

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => OrderStatus::PENDENTE->value,
            'total' => 599.80,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    public function test_authenticated_user_can_confirm_a_pending_order_and_reduce_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 10,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::PENDENTE,
            'total' => 599.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/confirm");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Pedido confirmado com sucesso.')
            ->assertJsonPath('data.status', OrderStatus::CONFIRMADO->value);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CONFIRMADO->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);

    }

    public function test_order_remains_pending_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 10,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::PENDENTE,
            'total' => 3288.90,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 11,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/confirm");

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.stock.0',
                'Estoque insuficiente para o produto Teclado mecânico.'
            );

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PENDENTE->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);

    }

    public function test_authenticated_user_can_cancel_a_pending_order_without_changing_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 10,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::PENDENTE,
            'total' => 599.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Pedido cancelado com sucesso.')
            ->assertJsonPath('data.status', OrderStatus::CANCELADO->value);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELADO->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);

    }

    public function test_authenticated_user_can_cancel_a_confirmed_order_and_restore_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 8,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::CONFIRMADO,
            'total' => 599.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Pedido cancelado com sucesso.')
            ->assertJsonPath('data.status', OrderStatus::CANCELADO->value);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELADO->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);

    }

    public function test_authenticated_user_cannot_confirm_an_already_confirmed_order(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 8,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::CONFIRMADO,
            'total' => 599.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/confirm");

        $response
        ->assertUnprocessable()
        ->assertJsonPath(
            'errors.order.0',
            'Somente pedidos pendentes podem ser confirmados.'
        );

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CONFIRMADO->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8,
        ]);



    }

    public function test_authenticated_user_cannot_cancel_an_already_cancelled_order(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Teclado mecânico',
            'price' => 299.90,
            'stock' => 10,
        ]);

        $order = $user->orders()->create([
            'status' => OrderStatus::CANCELADO,
            'total' => 599.80,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 299.90,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.order.0',
                'Este pedido já está cancelado.'
            );

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELADO->value,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);

    }



}
