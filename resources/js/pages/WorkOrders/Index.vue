<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { PhPencilSimple, PhTrash } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DataTable from '@/components/DataTable.vue'
import DeleteConfirmation from '@/components/DeleteConfirmation.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { edit as workOrdersEdit, destroy as workOrdersDestroy, create as workOrdersCreate } from '@/routes/work-orders'

const props = defineProps<{
    workOrders: any
    filters: any
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ordens de Trabalho', href: '/ordens-trabalho' },
]

const deleteWorkOrder = (id: number) => {
    router.delete(workOrdersDestroy(id).url, {
        onSuccess: () => {},
    })
}

const priorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgente': return 'destructive'
        case 'alta': return 'destructive'
        case 'normal': return 'secondary'
        case 'baixa': return 'outline'
        default: return 'secondary'
    }
}

const priorityLabel = (priority: string) => {
    switch (priority) {
        case 'urgente': return 'Urgente'
        case 'alta': return 'Alta'
        case 'normal': return 'Normal'
        case 'baixa': return 'Baixa'
        default: return priority
    }
}

const statusVariant = (status: string) => {
    switch (status) {
        case 'concluida': return 'default'
        case 'em_curso': return 'secondary'
        case 'pendente': return 'outline'
        case 'cancelada': return 'destructive'
        default: return 'secondary'
    }
}

const statusLabel = (status: string) => {
    switch (status) {
        case 'concluida': return 'Concluída'
        case 'em_curso': return 'Em Curso'
        case 'pendente': return 'Pendente'
        case 'cancelada': return 'Cancelada'
        default: return status
    }
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'number',
        header: 'Número',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('number')),
    },
    {
        accessorKey: 'entity.name',
        id: 'cliente',
        header: 'Cliente',
        enableSorting: false,
    },
    {
        accessorKey: 'order.number',
        id: 'encomenda',
        header: 'Encomenda',
        enableSorting: false,
        cell: ({ row }) => {
            const num = row.original.order?.number
            return num ? `ENC ${num}` : '-'
        }
    },
    {
        accessorKey: 'priority',
        header: 'Prioridade',
        enableSorting: false,
        cell: ({ row }) => {
            const priority = row.getValue('priority') as string
            const variant = priority === 'urgente' || priority === 'alta' ? 'destructive' : 'secondary'
            return h(
                Badge,
                { variant },
                () => priorityLabel(priority)
            )
        },
    },
    {
        accessorKey: 'users',
        id: 'technicians',
        header: 'Técnicos',
        enableSorting: false,
        cell: ({ row }) => {
            const users = row.original.users
            if (!users || users.length === 0) return '-'
            return users.map((u: any) => u.name).join(', ')
        }
    },
    {
        accessorKey: 'expected_duration',
        header: 'Duração Prev.',
        enableSorting: true,
        cell: ({ row }) => {
            const mins = row.getValue('expected_duration') as number | null
            if (!mins) return '-'
            const hours = Math.floor(mins / 60)
            const remaining = mins % 60
            return hours > 0 ? `${hours}h${remaining > 0 ? remaining + 'm' : ''}` : `${remaining}m`
        },
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.getValue('status') as string
            return h(
                Badge,
                {
                    variant: statusVariant(status) as any,
                    class: status === 'concluida' ? 'bg-emerald-500 hover:bg-emerald-600' : '',
                },
                () => statusLabel(status)
            )
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const item = row.original
            return h('div', { class: 'flex items-center justify-end gap-3' }, [
                h(
                    Link,
                    {
                        href: workOrdersEdit(item.id).url,
                        title: 'Editar',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteWorkOrder(item.id),
                }, {
                    trigger: () => h(
                        'button',
                        {
                            title: 'Eliminar',
                            class: 'text-muted-foreground hover:text-destructive transition-colors',
                        },
                        [h(PhTrash, { class: 'size-4' })]
                    )
                }),
            ])
        },
    },
]
</script>

<template>
    <Head title="Ordens de Trabalho" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Ordens de Trabalho
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de ordens de trabalho para a equipa técnica.
                    </p>
                </div>
                <Link :href="workOrdersCreate().url">
                    <Button>Nova Ordem de Trabalho</Button>
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :paginated="workOrders"
                :filters="filters"
                search-placeholder="Pesquisar ordens de trabalho..."
            />
        </div>
    </AppLayout>
</template>
