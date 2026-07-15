<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { PhPlus, PhPencilSimple, PhTrash, PhFilePdf, PhArrowsClockwise } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { h, computed } from 'vue'
import DataTable from '@/components/DataTable.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import AppLayout from '@/layouts/AppLayout.vue'
import * as clientRoutes from '@/routes/encomendas'
import * as supplierRoutes from '@/routes/supplier-orders'

const props = withDefaults(
    defineProps<{
        orders: {
            data: any[]
            current_page: number
            last_page: number
            per_page: number
            total: number
            from: number | null
            to: number | null
        }
        filters: Record<string, any>
        type?: 'cliente' | 'fornecedor'
    }>(),
    {
        type: 'cliente',
    }
)

const isSupplier = computed(() => props.type === 'fornecedor')
const routes = computed(() => isSupplier.value ? supplierRoutes : clientRoutes)

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: isSupplier.value ? 'Encomendas Fornecedores' : 'Encomendas Clientes', href: isSupplier.value ? '/encomendas-fornecedores' : '/encomendas' },
])

function deleteOrder(id: number) {
    const msg = isSupplier.value
        ? 'Tem a certeza que deseja eliminar esta encomenda de fornecedor?'
        : 'Tem a certeza que deseja eliminar esta encomenda de cliente?'

    if (confirm(msg)) {
        const args = isSupplier.value
            ? { encomendas_fornecedore: id }
            : { encomenda: id }

        router.delete((routes.value.destroy as any).url(args), {
            preserveScroll: true,
        })
    }
}

function convertToSuppliers(id: number) {
    if (confirm('Pretende gerar Encomendas de Fornecedor a partir desta encomenda?')) {
        // Only client orders can be converted
        if (!isSupplier.value && 'convert' in routes.value) {
            router.post((routes.value as any).convert.url(id), {}, {
                preserveScroll: true,
            })
        }
    }
}

const columns = computed<ColumnDef<any>[]>(() => [
    {
        accessorKey: 'order_date',
        header: 'Data',
        enableSorting: true,
        cell: ({ row }) => {
            const date = row.getValue('order_date') as string

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
        accessorKey: 'entity_id',
        header: isSupplier.value ? 'Fornecedor' : 'Cliente',
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

            const editArgs = isSupplier.value
                ? { encomendas_fornecedore: id }
                : { encomenda: id }

            return h('div', { class: 'flex items-center justify-end gap-3' }, [
                // Download PDF
                h(
                    'a',
                    {
                        href: (routes.value.pdf as any).url(id),
                        target: '_blank',
                        title: 'Descarregar PDF',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    h(PhFilePdf, { class: 'size-4' })
                ),
                // Converter em Encomendas de Fornecedor (só quando fechado e apenas para clientes)
                ...(!isSupplier.value && status === 'fechado'
                    ? [
                          h(
                              'button',
                              {
                                  onClick: () => convertToSuppliers(id),
                                  title: 'Gerar Encomendas de Fornecedor',
                                  class: 'text-muted-foreground hover:text-emerald-600 transition-colors',
                              },
                              h(PhArrowsClockwise, { class: 'size-4' })
                          ),
                      ]
                    : []),
                // Editar
                h(
                    Link,
                    {
                        href: (routes.value.edit as any).url(editArgs),
                        title: 'Editar',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                ),
                // Eliminar
                h(
                    'button',
                    {
                        onClick: () => deleteOrder(id),
                        title: 'Eliminar',
                        class: 'text-muted-foreground hover:text-destructive transition-colors',
                    },
                    () => h(PhTrash, { class: 'size-4' })
                ),
            ])
        },
    },
])
</script>

<template>
    <Head :title="isSupplier ? 'Encomendas - Fornecedores' : 'Encomendas - Clientes'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        {{ isSupplier ? 'Encomendas - Fornecedores' : 'Encomendas - Clientes' }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isSupplier ? 'Gestão de encomendas a fornecedores (compras).' : 'Gestão de encomendas de clientes (vendas).' }}
                    </p>
                </div>
                <Link :href="routes.create.url()">
                    <Button class="gap-2">
                        <PhPlus class="size-4" weight="bold" />
                        Nova Encomenda
                    </Button>
                </Link>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :paginated="orders"
                :filters="filters"
                searchPlaceholder="Pesquisar por número..."
            />
        </div>
    </AppLayout>
</template>
