<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PhPlus } from '@phosphor-icons/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    calendarTypes: {
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
    { title: 'Agenda', href: '#' },
    { title: 'Tipos de Eventos', href: '/agenda/tipos-de-eventos' },
];

function deleteCalendarType(id: number) {
    router.delete(route('calendar-types.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;

            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Tipo de evento eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o tipo de evento.');
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
                    href: route('calendar-types.edit', row.original.id),
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
            const calendarType = row.original;

            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: route('calendar-types.edit', calendarType.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteCalendarType(calendarType.id),
                    description: 'Esta ação irá eliminar permanentemente o tipo de evento, caso este não esteja em uso.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Tipos de Eventos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Tipos de Eventos
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão dos tipos de eventos.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('calendar-types.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Tipo de Evento
                    </Link>
                </Button>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="(columns as any)"
                :paginated="calendarTypes"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome..."
            />
        </div>
    </AppLayout>
</template>
