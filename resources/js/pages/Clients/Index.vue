<script setup lang="ts">
import { h } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Button } from '@/components/ui/button';
import { PhPlus } from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';
import type { ColumnDef } from '@tanstack/vue-table';

const props = defineProps<{
    clients: {
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
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Clientes',
        href: '/clientes',
    },
];

function deleteClient(id: number) {
    router.delete((window as any).route('clients.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Cliente eliminado com sucesso.');
        },
        onError: () => {
            toast.error('Erro ao eliminar o cliente.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'nif',
        header: 'NIF',
        enableSorting: false,
        cell: ({ row }) => {
            const client = row.original;
            const wRoute = (window as any).route;
            return h(
                Link,
                {
                    href: wRoute('clients.show', client.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => client.nif,
            );
        },
    },
    {
        accessorKey: 'name',
        header: 'Nome do Cliente',
        enableSorting: true,
        cell: ({ row }) => {
            const client = row.original;
            const wRoute = (window as any).route;
            return h(
                Link,
                {
                    href: wRoute('clients.show', client.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => client.name,
            );
        },
    },
    {
        accessorKey: 'phone',
        header: 'Telefone',
        enableSorting: false,
    },
    {
        accessorKey: 'mobile',
        header: 'Telemóvel',
        enableSorting: false,
    },
    {
        accessorKey: 'website',
        header: 'Website',
        enableSorting: false,
        cell: ({ row }) => {
            const website = row.getValue('website') as string;
            if (!website) return '-';
            return h(
                'a',
                {
                    href: website,
                    target: '_blank',
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                website,
            );
        },
    },
    {
        accessorKey: 'email',
        header: 'Email',
        enableSorting: false,
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.getValue('status');
            const isActive = status === 1 || status === true || status === 'active';

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
                isActive ? 'Ativo' : 'Inativo',
            );
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const client = row.original;
            const wRoute = (window as any).route;
            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: wRoute('clients.edit', client.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteClient(client.id),
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Clientes
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão da carteira de clientes e entidades associadas.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('clients.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Cliente
                    </Link>
                </Button>
            </div>

            <!-- Componente DataTable -->
            <DataTable
                :columns="columns"
                :paginated="clients"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome, NIF ou email..."
            />
        </div>
    </AppLayout>
</template>
