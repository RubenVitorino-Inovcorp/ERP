<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'clientes',
            'fornecedores',
            'contactos',
            'propostas',
            'calendario',
            'encomendas_clientes',
            'encomendas_fornecedores',
            'ordens_trabalho',
            'financeiro',
            'arquivo_digital',
            'utilizadores',
            'permissoes',
            'configuracoes'
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}"]);
            }
        }

        // Criar a role de Admin com todas as permissões (opcional, para conveniência inicial)
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'status' => true]);
        $adminRole->syncPermissions(Permission::all());
    }
}
