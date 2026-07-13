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
    vatRates: {
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
    { title: 'Configurações', href: '#' },
    { title: 'Taxas de IVA', href: '/configuracoes/financeiro/iva' },
];

function deleteVatRate(id: number) {
    router.delete(route('vat-rates.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Se houver uma mensagem de erro na sessão do Laravel
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Taxa de IVA eliminada com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar a taxa de IVA.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        enableSorting: true,
        cell: ({ row }) => {
            return h(
                Link,
                {
                    href: route('vat-rates.edit', row.original.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => row.original.name,
            );
        },
    },
    {
        accessorKey: 'value',
        header: 'Valor (%)',
        enableSorting: true,
        cell: ({ row }) => {
            const val = parseFloat(row.original.value);
            return h('span', `${val}%`);
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const vatRate = row.original;
            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: route('vat-rates.edit', vatRate.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteVatRate(vatRate.id),
                    description: 'Esta ação irá eliminar permanentemente a taxa de IVA, caso esta não esteja em uso.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Taxas de IVA" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Taxas de IVA
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de taxas de IVA aplicadas a produtos e serviços do sistema.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('vat-rates.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Taxa
                    </Link>
                </Button>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="(columns as any)"
                :paginated="vatRates"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome..."
            />
        </div>
    </AppLayout>
</template>
