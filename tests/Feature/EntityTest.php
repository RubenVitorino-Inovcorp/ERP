<?php

use App\Models\User;
use App\Models\Entity;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login for clients', function () {
    $this->get(route('clients.index'))->assertRedirect(route('login'));
});

test('authenticated users can visit clients index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('clients.index'));
    $response->assertSuccessful();
});

test('authenticated users can create a client', function () {
    $user = User::factory()->create();
    $country = Country::create(['name' => 'Portugal', 'code' => 'PT']);
    $this->actingAs($user);
    $response = $this->post(route('clients.store'), [
        'type' => 'cliente',
        'nif' => '500000000', // Válido estruturalmente em PT
        'name' => 'Cliente Teste Lda',
        'address' => 'Rua Principal, 45',
        'zip_code' => '1200-001',
        'city' => 'Lisboa',
        'country_id' => $country->id,
        'email' => 'cliente@teste.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    $response->assertRedirect(route('clients.index'));
    
    $entity = Entity::where('name', 'Cliente Teste Lda')->first();
    expect($entity)->not->toBeNull();
    expect($entity->email)->toBe('cliente@teste.com');
    expect($entity->nif)->toBe('500000000');
});

test('authenticated users can visit suppliers index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('suppliers.index'));
    $response->assertSuccessful();
});

test('authenticated users can create a supplier', function () {
    $user = User::factory()->create();
    $country = Country::create(['name' => 'Portugal', 'code' => 'PT']);
    $this->actingAs($user);

    $response = $this->post(route('suppliers.store'), [
        'type' => 'fornecedor',
        'nif' => '500000000',
        'name' => 'Fornecedor Teste Lda',
        'address' => 'Zona Industrial, Lote 12',
        'zip_code' => '4000-002',
        'city' => 'Porto',
        'country_id' => $country->id,
        'email' => 'fornecedor@teste.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    $response->assertRedirect(route('suppliers.index'));
    
    $entity = Entity::where('name', 'Fornecedor Teste Lda')->first();
    expect($entity)->not->toBeNull();
    expect($entity->email)->toBe('fornecedor@teste.com');
    expect($entity->nif)->toBe('500000000');
});

test('nif must be unique in entities', function () {
    $user = User::factory()->create();
    $country = Country::create(['name' => 'Portugal', 'code' => 'PT']);
    
    // Criar a primeira entidade
    Entity::create([
        'type' => 'cliente',
        'number' => 1,
        'nif' => '500000000',
        'name' => 'Primeiro Cliente',
        'address' => 'Morada 1',
        'zip_code' => '1000-001',
        'city' => 'Lisboa',
        'country_id' => $country->id,
        'email' => 'primeiro@cliente.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    $this->actingAs($user);

    // Tentar criar outra entidade com o mesmo NIF
    $response = $this->post(route('clients.store'), [
        'type' => 'cliente',
        'nif' => '500000000', // NIF duplicado
        'name' => 'Segundo Cliente',
        'address' => 'Morada 2',
        'zip_code' => '1000-002',
        'city' => 'Porto',
        'country_id' => $country->id,
        'email' => 'segundo@cliente.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    $response->assertSessionHasErrors('nif');
});
