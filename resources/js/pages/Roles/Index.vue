<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PhPlus } from '@phosphor-icons/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    roles: {
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
    { title: 'Permissões', href: '/gestao-acessos/permissoes' },
];

function deleteRole(id: number) {
    router.delete(route('roles.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;

            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Grupo eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o grupo.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome do Grupo',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'users_count',
        header: 'Utilizadores Relacionados',
        enableSorting: false,
        cell: ({ row }) => h('span', {}, `${row.original.users_count || 0} utilizadores`),
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        cell: ({ row }) => {
            const isActive = row.original.status;

            return h(
                Badge,
                { variant: isActive ? 'default' : 'secondary' },
                () => (isActive ? 'Ativo' : 'Inativo')
            )
        },
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) => {
            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Link, {
                    href: route('roles.edit', row.original.id),
                    class: 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8',
                }, () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', width: '1em', height: '1em', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [
                    h('path', { d: 'M12 20h9' }),
                    h('path', { d: 'M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z' })
                ])),
                h(DeleteConfirmation, {
                    title: 'Eliminar Grupo',
                    description: `Tem a certeza que deseja eliminar o grupo "${row.original.name}"? Não será possível se este tiver utilizadores atribuídos.`,
                    onConfirm: () => deleteRole(row.original.id)
                })
            ]);
        },
    },
];
</script>

<template>
    <Head title="Permissões" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Permissões (Grupos)
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de grupos e das suas respetivas permissões no sistema.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('roles.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Novo Grupo
                    </Link>
                </Button>
            </div>
            
            <DataTable 
                :columns="columns" 
                :paginated="roles" 
                :filters="filters"
                searchPlaceholder="Pesquisar pelo nome do grupo..."
            />
        </div>
    </AppLayout>
</template>
