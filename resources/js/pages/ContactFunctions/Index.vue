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
    contactFunctions: {
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
    { title: 'Funções de Contacto', href: '/configuracoes/contactos/funcoes' },
];

function deleteContactFunction(id: number) {
    router.delete(route('contact-functions.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Função de contacto eliminada com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar a função de contacto.');
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
                    href: route('contact-functions.edit', row.original.id),
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
            const contactFunction = row.original;
            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: route('contact-functions.edit', contactFunction.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteContactFunction(contactFunction.id),
                    description: 'Esta ação irá eliminar permanentemente a função de contacto, caso esta não esteja em uso.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Funções de Contacto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Funções de Contacto
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão das funções e cargos atribuídos aos contactos das entidades.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('contact-functions.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Função
                    </Link>
                </Button>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="(columns as any)"
                :paginated="contactFunctions"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome..."
            />
        </div>
    </AppLayout>
</template>
