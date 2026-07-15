<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { PhPlus, PhPencilSimple, PhTrash, PhFilePdf, PhArrowRight } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DataTable from '@/components/DataTable.vue'
import DeleteConfirmation from '@/components/DeleteConfirmation.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { create as proposalCreate, edit as proposalEdit, destroy as proposalDestroy } from '@/routes/proposals'

const props = defineProps<{
    proposals: {
        data: any[]
        current_page: number
        last_page: number
        per_page: number
        total: number
        from: number | null
        to: number | null
    }
    filters: Record<string, any>
}>()

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Propostas',
        href: '/propostas',
    },
]

function deleteProposal(id: number) {
    router.delete(proposalDestroy(id), {
        preserveScroll: true,
    })
}

function convertToOrder(id: number) {
    if (confirm('Pretende converter esta proposta numa Encomenda de Cliente?')) {
        router.post(`/propostas/${id}/convert-to-order`, {}, {
            preserveScroll: true,
        })
    }
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'proposal_date',
        header: 'Data',
        enableSorting: true,
        cell: ({ row }) => {
            const date = row.getValue('proposal_date') as string

            return h('span', date ? new Date(date).toLocaleDateString('pt-PT') : '-')
        },
    },
    {
        accessorKey: 'number',
        header: 'Número',
        enableSorting: true,
        cell: ({ row }) => {
            return h('span', { class: 'font-semibold' }, `#${row.getValue('number')}`)
        },
    },
    {
        accessorKey: 'validity_date',
        header: 'Validade',
        enableSorting: true,
        cell: ({ row }) => {
            const date = row.getValue('validity_date') as string

            return h('span', date ? new Date(date).toLocaleDateString('pt-PT') : '-')
        },
    },
    {
        accessorKey: 'entity_id',
        header: 'Cliente',
        enableSorting: false,
        cell: ({ row }) => {
            const entity = row.original.entity

            return h('span', { class: 'font-medium' }, entity ? entity.name : '-')
        },
    },
    {
        accessorKey: 'total_value',
        header: 'Valor Total',
        enableSorting: false,
        cell: ({ row }) => {
            const val = row.original.total_value || 0

            return h('span', { class: 'font-semibold text-foreground' }, `${val.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`)
        },
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.getValue('status') as string
            const isClosed = status === 'fechado'

            return h(
                Badge,
                { variant: isClosed ? 'default' : 'secondary' },
                () => (isClosed ? 'Fechado' : 'Rascunho')
            )
        },
    },
    {
        id: 'actions',
        enableSorting: false,
        cell: ({ row }) => {
            const id = row.original.id
            const status = row.original.status

            return h('div', { class: 'flex items-center justify-end gap-3' }, [
                // Download PDF
                h(
                    'a',
                    {
                        href: `/propostas/${id}/pdf`,
                        target: '_blank',
                        title: 'Descarregar PDF',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    [h(PhFilePdf, { class: 'size-4' })]
                ),
                // Converter em Encomenda
                h(
                    'button',
                    {
                        onClick: () => convertToOrder(id),
                        title: 'Converter em Encomenda',
                        class: 'text-muted-foreground hover:text-emerald-600 transition-colors',
                    },
                    [h(PhArrowRight, { class: 'size-4' })]
                ),
                // Editar
                h(
                    Link,
                    {
                        href: proposalEdit(id),
                        title: 'Editar',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                ),
                // Eliminar
                h(DeleteConfirmation, {
                    onConfirm: () => deleteProposal(id),
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
    <Head title="Propostas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Propostas Comercial
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão e emissão de propostas de venda para clientes.
                    </p>
                </div>
                <Link :href="proposalCreate()">
                    <Button class="gap-2">
                        <PhPlus class="size-4" weight="bold" />
                        Nova Proposta
                    </Button>
                </Link>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :paginated="proposals"
                :filters="filters"
                searchPlaceholder="Pesquisar por número ou cliente..."
            />
        </div>
    </AppLayout>
</template>
