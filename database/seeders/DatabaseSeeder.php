<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\BankAccount;
use App\Models\CalendarAction;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactFunction;
use App\Models\CurrentAccountEntry;
use App\Models\DigitalFile;
use App\Models\DocumentCategory;
use App\Models\Entity;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Proposal;
use App\Models\ProposalLine;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Models\VatRate;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador base
        User::factory()->create([
            'name' => 'Admin Inovcorp',
            'email' => 'admin@inovcorp.pt',
            'password' => bcrypt('password'),
        ]);

        // Outros users
        User::factory(5)->create();

        // Países
        $this->call([
            CountrySeeder::class,
        ]);

        // Configurações
        VatRate::factory(3)->create();
        DocumentCategory::factory(4)->create();
        CalendarType::factory(4)->create();
        CalendarAction::factory(5)->create();
        ContactFunction::factory(5)->create();
        Company::factory()->create();
        BankAccount::factory(3)->create();

        // Entidades (Clientes e Fornecedores)
        $entities = Entity::factory(20)->create();

        // Contactos para as entidades
        foreach ($entities as $entity) {
            Contact::factory(rand(1, 3))->create([
                'entity_id' => $entity->id,
            ]);

            // Ficheiros digitais
            DigitalFile::factory(rand(0, 2))->create();

            // Conta corrente
            CurrentAccountEntry::factory(rand(1, 4))->create([
                'entity_id' => $entity->id,
            ]);
        }

        // Artigos
        Article::factory(30)->create();

        // Eventos no Calendário
        CalendarEvent::factory(20)->create();

        // Propostas
        $proposals = Proposal::factory(15)->create();
        foreach ($proposals as $proposal) {
            ProposalLine::factory(rand(1, 5))->create([
                'proposal_id' => $proposal->id,
            ]);
        }

        // Encomendas
        $orders = Order::factory(15)->create();
        foreach ($orders as $order) {
            OrderLine::factory(rand(1, 5))->create([
                'order_id' => $order->id,
            ]);

            // Ordens de Trabalho (para algumas encomendas de clientes)
            if (rand(1, 100) <= 60 && $order->type === 'cliente') {
                WorkOrder::factory()->create([
                    'order_id' => $order->id,
                    'entity_id' => $order->entity_id,
                ]);
            }
        }

        // Faturas Fornecedor
        SupplierInvoice::factory(15)->create();
    }
}
