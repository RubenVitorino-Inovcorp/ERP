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
    countries: {
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
    { title: 'Países', href: '/configuracoes/paises' },
];

function deleteCountry(id: number) {
    router.delete(route('countries.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('País eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o país.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'País',
        enableSorting: true,
        cell: ({ row }) => {
            return h(
                Link,
                {
                    href: route('countries.edit', row.original.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => row.original.name,
            );
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const country = row.original;
            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: route('countries.edit', country.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteCountry(country.id),
                    description: 'Esta ação irá eliminar permanentemente o país.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Países" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Países
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão dos países.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('countries.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar País
                    </Link>
                </Button>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="(columns as any)"
                :paginated="countries"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome..."
            />
        </div>
    </AppLayout>
</template>
