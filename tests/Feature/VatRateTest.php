<?php

use App\Models\Article;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login for vat rates', function () {
    $this->get(route('vat-rates.index'))->assertRedirect(route('login'));
});

test('authenticated users can visit vat rates index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('vat-rates.index'));
    $response->assertSuccessful();
});

test('authenticated users can create a vat rate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('vat-rates.store'), [
        'name' => 'IVA 23%',
        'value' => 23.00,
    ]);

    $response->assertRedirect(route('vat-rates.index'));
    $this->assertDatabaseHas('vat_rates', [
        'name' => 'IVA 23%',
        'value' => 23.00,
    ]);
});

test('vat rate name must be unique', function () {
    $user = User::factory()->create();
    VatRate::create(['name' => 'IVA 23%', 'value' => 23.00]);

    $this->actingAs($user);

    $response = $this->post(route('vat-rates.store'), [
        'name' => 'IVA 23%', // Duplicado
        'value' => 6.00,
    ]);

    $response->assertSessionHasErrors('name');
});

test('authenticated users can update a vat rate', function () {
    $user = User::factory()->create();
    $vatRate = VatRate::create(['name' => 'IVA 23%', 'value' => 23.00]);

    $this->actingAs($user);

    $response = $this->put(route('vat-rates.update', $vatRate->id), [
        'name' => 'IVA Normal',
        'value' => 23.50,
    ]);

    $response->assertRedirect(route('vat-rates.index'));
    $this->assertDatabaseHas('vat_rates', [
        'id' => $vatRate->id,
        'name' => 'IVA Normal',
        'value' => 23.50,
    ]);
});

test('authenticated users can delete a vat rate', function () {
    $user = User::factory()->create();
    $vatRate = VatRate::create(['name' => 'IVA 23%', 'value' => 23.00]);

    $this->actingAs($user);

    $response = $this->delete(route('vat-rates.destroy', $vatRate->id));

    $response->assertRedirect(route('vat-rates.index'));
    $this->assertDatabaseMissing('vat_rates', [
        'id' => $vatRate->id,
    ]);
});

test('cannot delete a vat rate if it is in use by an article', function () {
    $user = User::factory()->create();
    $vatRate = VatRate::create(['name' => 'IVA 23%', 'value' => 23.00]);

    // Criar um artigo que usa esta taxa de IVA
    Article::create([
        'reference' => 'ART001',
        'name' => 'Artigo Exemplo',
        'price' => 10.00,
        'vat_rate_id' => $vatRate->id,
        'status' => true,
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('vat-rates.destroy', $vatRate->id));

    $response->assertRedirect(route('vat-rates.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('vat_rates', [
        'id' => $vatRate->id,
    ]);
});
