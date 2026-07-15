<?php

use App\Models\Contact;
use App\Models\ContactFunction;
use App\Models\Country;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login for contact functions', function () {
    $this->get(route('contact-functions.index'))->assertRedirect(route('login'));
});

test('authenticated users can visit contact functions index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('contact-functions.index'));
    $response->assertSuccessful();
});

test('authenticated users can create a contact function', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('contact-functions.store'), [
        'name' => 'Diretor Financeiro',
    ]);

    $response->assertRedirect(route('contact-functions.index'));
    $this->assertDatabaseHas('contact_functions', [
        'name' => 'Diretor Financeiro',
    ]);
});

test('contact function name must be unique', function () {
    $user = User::factory()->create();
    ContactFunction::create(['name' => 'Diretor Financeiro']);

    $this->actingAs($user);

    $response = $this->post(route('contact-functions.store'), [
        'name' => 'Diretor Financeiro', // Duplicado
    ]);

    $response->assertSessionHasErrors('name');
});

test('authenticated users can update a contact function', function () {
    $user = User::factory()->create();
    $func = ContactFunction::create(['name' => 'Gerente']);

    $this->actingAs($user);

    $response = $this->put(route('contact-functions.update', $func->id), [
        'name' => 'Gerente Geral',
    ]);

    $response->assertRedirect(route('contact-functions.index'));
    $this->assertDatabaseHas('contact_functions', [
        'id' => $func->id,
        'name' => 'Gerente Geral',
    ]);
});

test('authenticated users can delete a contact function', function () {
    $user = User::factory()->create();
    $func = ContactFunction::create(['name' => 'Comercial']);

    $this->actingAs($user);

    $response = $this->delete(route('contact-functions.destroy', $func->id));

    $response->assertRedirect(route('contact-functions.index'));
    $this->assertDatabaseMissing('contact_functions', [
        'id' => $func->id,
    ]);
});

test('cannot delete a contact function if it is in use by a contact', function () {
    $user = User::factory()->create();
    $func = ContactFunction::create(['name' => 'Comercial']);

    // Criar entidades e país para associar o contacto
    $country = Country::create(['name' => 'Portugal', 'code' => 'PT']);
    $entity = Entity::create([
        'type' => 'cliente',
        'number' => 1,
        'nif' => '500000000',
        'name' => 'Cliente Teste',
        'address' => 'Rua Principal',
        'zip_code' => '1000-001',
        'city' => 'Lisboa',
        'country_id' => $country->id,
        'email' => 'cliente@teste.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    // Criar contacto
    Contact::create([
        'number' => 1,
        'entity_id' => $entity->id,
        'name' => 'João',
        'last_name' => 'Silva',
        'contact_function_id' => $func->id,
        'email' => 'joao@silva.com',
        'gdpr_consent' => true,
        'status' => true,
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('contact-functions.destroy', $func->id));

    $response->assertRedirect(route('contact-functions.index'));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('contact_functions', [
        'id' => $func->id,
    ]);
});
