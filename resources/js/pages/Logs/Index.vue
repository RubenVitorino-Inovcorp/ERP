<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DataTable from '@/components/DataTable.vue'
import { Badge } from '@/components/ui/badge'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
    logs: any
    filters: any
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Logs', href: '/logs' },
]

const actionLabel = (action: string) => {
    switch (action) {
        case 'created': return 'Criação'
        case 'updated': return 'Atualização'
        case 'deleted': return 'Eliminação'
        default: return action
    }
}

const actionVariant = (action: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (action) {
        case 'created': return 'default'
        case 'updated': return 'secondary'
        case 'deleted': return 'destructive'
        default: return 'outline'
    }
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'data',
        header: 'Data',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.getValue('data')),
    },
    {
        accessorKey: 'hora',
        header: 'Hora',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.getValue('hora')),
    },
    {
        accessorKey: 'utilizador',
        header: 'Utilizador',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm font-medium' }, row.getValue('utilizador')),
    },
    {
        accessorKey: 'menu',
        header: 'Menu',
        enableSorting: true,
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => row.getValue('menu')),
    },
    {
        accessorKey: 'accao',
        header: 'Acção',
        enableSorting: false,
        cell: ({ row }) => {
            const action = row.getValue('accao') as string
            return h(
                Badge,
                { variant: actionVariant(action) as any },
                () => actionLabel(action)
            )
        },
    },
    {
        accessorKey: 'dispositivo',
        header: 'Dispositivo',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.getValue('dispositivo')),
    },
    {
        accessorKey: 'ip',
        header: 'IP',
        enableSorting: false,
        cell: ({ row }) => h(
            'code',
            { class: 'rounded bg-muted px-1.5 py-0.5 text-xs font-mono' },
            row.getValue('ip')
        ),
    },
]
</script>

<template>
    <Head title="Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    Logs
                </h1>
                <p class="text-sm text-muted-foreground">
                    Registo de atividades realizadas no sistema.
                </p>
            </div>

            <DataTable
                :columns="columns"
                :paginated="logs"
                :filters="filters"
                search-placeholder="Pesquisar logs..."
            />
        </div>
    </AppLayout>
</template>
