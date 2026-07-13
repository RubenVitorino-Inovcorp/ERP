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
    documentCategories: {
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
    { title: 'Categorias de Documentos', href: '/configuracoes/categorias-documentos' },
];

function deleteCategory(id: number) {
    router.delete(route('document-categories.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Categoria eliminada com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar a categoria.');
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
                    href: route('document-categories.edit', row.original.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => row.original.name,
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
            );
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const category = row.original;
            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: route('document-categories.edit', category.id),
                        class: 'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteCategory(category.id),
                    description: 'Esta ação irá eliminar permanentemente a categoria, caso não esteja em uso.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Categorias de Documentos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Categorias de Documentos
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão das categorias disponíveis para o arquivo digital.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('document-categories.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Nova Categoria
                    </Link>
                </Button>
            </div>

            <DataTable
                :columns="(columns as any)"
                :paginated="documentCategories"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome..."
            />
        </div>
    </AppLayout>
</template>
