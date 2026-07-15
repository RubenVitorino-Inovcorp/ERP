<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { PhPlus, PhPencilSimple, PhTrash } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import DataTable from '@/components/DataTable.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import AppLayout from '@/layouts/AppLayout.vue'
import { create as contactsCreate, edit as contactsEdit, destroy as contactsDestroy } from '@/routes/contacts'

const props = defineProps<{
    contacts: {
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
        title: 'Contactos',
        href: '/contacts',
    },
]

function deleteContact(id: number) {
    if (confirm('Tem a certeza que deseja eliminar este contacto?')) {
        router.delete(contactsDestroy({ contact: id }), {
            preserveScroll: true,
        })
    }
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        enableSorting: true,
    },
    {
        accessorKey: 'last_name',
        header: 'Apelido',
        enableSorting: true,
    },
    {
        accessorKey: 'contact_function_id',
        header: 'Função',
        enableSorting: false,
        cell: ({ row }) => {
            const contactFunction = row.original.contact_function

            return h('span', contactFunction ? contactFunction.name : '-')
        },
    },
    {
        accessorKey: 'entity_id',
        header: 'Entidade',
        enableSorting: false,
        cell: ({ row }) => {
            const entity = row.original.entity

            return h('span', entity ? entity.name : '-')
        },
    },
    {
        accessorKey: 'phone',
        header: 'Telefone',
        enableSorting: false,
        cell: ({ row }) => {
            return h('span', row.getValue('phone') || '-')
        },
    },
    {
        accessorKey: 'mobile',
        header: 'Telemóvel',
        enableSorting: false,
        cell: ({ row }) => {
            return h('span', row.getValue('mobile') || '-')
        },
    },
    {
        accessorKey: 'email',
        header: 'Email',
        enableSorting: false,
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.getValue('status')
            const isActive = status === 1 || status === true || status === 'active';

            return h(
                Badge,
                { variant: isActive ? 'default' : 'secondary' },
                () => (isActive ? 'Ativo' : 'Inativo')
            )
        },
    },
    {
        id: 'actions',
        enableSorting: false,
        cell: ({ row }) => {
            const id = row.original.id

            return h('div', { class: 'flex items-center justify-end gap-2' }, [
                h(
                    Link,
                    {
                        href: contactsEdit({ contact: id }),
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                ),
                h(
                    'button',
                    {
                        onClick: () => deleteContact(id),
                        class: 'text-muted-foreground hover:text-destructive transition-colors',
                    },
                    () => h(PhTrash, { class: 'size-4' })
                ),
            ])
        },
    },
]
</script>

<template>
    <Head title="Contactos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Contactos
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão da lista de contactos das entidades.
                    </p>
                </div>
                <Link :href="contactsCreate()">
                    <Button class="gap-2">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Contacto
                    </Button>
                </Link>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :paginated="contacts"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome, apelido, email ou telefone..."
            />
        </div>
    </AppLayout>
</template>
