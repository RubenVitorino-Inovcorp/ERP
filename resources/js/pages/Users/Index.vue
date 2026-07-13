<script setup lang="ts">
import { h } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PhPlus } from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';
import type { ColumnDef } from '@tanstack/vue-table';

const props = defineProps<{
    users: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: Record<string, any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Gestão de Acessos', href: '#' },
    { title: 'Utilizadores', href: '/gestao-acessos/utilizadores' },
];

function deleteUser(id: number) {
    router.delete(route('users.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Utilizador eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o utilizador.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'email',
        header: 'Email',
    },
    {
        accessorKey: 'phone',
        header: 'Telemóvel',
        cell: ({ row }) => row.original.phone || '-',
    },
    {
        id: 'roles',
        header: 'Grupo de Permissões',
        cell: ({ row }) => {
            const roles = row.original.roles || [];
            if (roles.length === 0) return h('span', { class: 'text-muted-foreground italic' }, 'Sem grupo');
            
            return h('div', { class: 'flex flex-wrap gap-1' }, 
                roles.map((r: any) => h(Badge, { variant: 'secondary' }, () => r.name))
            );
        },
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        cell: ({ row }) => {
            const isActive = row.original.status;
            return h(
                'span',
                {
                    class: [
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                        isActive
                            ? 'bg-[var(--sidebar)]/10 text-[var(--sidebar)] dark:bg-[var(--sidebar-primary)]/10 dark:text-[var(--sidebar-primary)]'
                            : 'bg-muted text-muted-foreground',
                    ],
                },
                isActive ? 'Ativo' : 'Inativo'
            )
        },
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) => {
            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Link, {
                    href: route('users.edit', row.original.id),
                    class: 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8',
                }, () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: '1em', height: '1em', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
                    h('path', { d: 'M12 20h9' }),
                    h('path', { d: 'M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z' })
                ])),
                h(DeleteConfirmation, {
                    title: 'Eliminar Utilizador',
                    description: `Tem a certeza que deseja eliminar o utilizador "${row.original.name}"? Esta ação não pode ser desfeita.`,
                    onConfirm: () => deleteUser(row.original.id)
                })
            ]);
        },
    },
];
</script>

<template>
    <Head title="Utilizadores" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Utilizadores
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de utilizadores e grupos de permissões.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('users.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Novo Utilizador
                    </Link>
                </Button>
            </div>
            
            <!-- DataTable Component -->
            <DataTable 
                :columns="columns" 
                :paginated="users" 
                :filters="filters"
                searchPlaceholder="Pesquisar por nome, email ou telemóvel..."
            />
        </div>
    </AppLayout>
</template>
