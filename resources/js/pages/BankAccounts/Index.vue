<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PhPlus, PhPencilSimple, PhTrash } from '@phosphor-icons/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { toUrl } from '@/lib/utils';
import { index as bankAccountsIndex, create as bankAccountsCreate, edit as bankAccountsEdit, destroy as bankAccountsDestroy } from '@/routes/contas-bancarias';

defineProps<{
    bankAccounts: any;
    filters: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '#' },
    { title: 'Contas Bancárias', href: toUrl(bankAccountsIndex) },
];

function deleteBankAccount(id: number) {
    router.delete(bankAccountsDestroy(id).url, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Conta bancária eliminada com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar a conta bancária.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome da Conta',
        cell: ({ row }) => h('span', { class: 'font-semibold' }, row.getValue('name')),
    },
    {
        accessorKey: 'bank_name',
        header: 'Banco',
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('bank_name')),
    },
    { accessorKey: 'iban', header: 'IBAN' },
    { accessorKey: 'swift', header: 'SWIFT/BIC' },
    {
        accessorKey: 'status',
        header: 'Estado',
        cell: ({ row }) => {
            const status = row.getValue('status');
            const isActive = status === 1 || status === true;

            return h(
                Badge,
                { variant: isActive ? 'default' : 'secondary' },
                () => (isActive ? 'Ativo' : 'Inativo')
            );
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const item = row.original;

            return h('div', { class: 'flex items-center justify-end gap-3' }, [
                h(
                    Link,
                    {
                        href: bankAccountsEdit(item.id).url,
                        title: 'Editar',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteBankAccount(item.id),
                }, {
                    trigger: () => h(
                        'button',
                        {
                            title: 'Eliminar',
                            class: 'text-muted-foreground hover:text-destructive transition-colors',
                        },
                        [h(PhTrash, { class: 'size-4' })]
                    )
                })
            ]);
        },
    },
];
</script>

<template>
    <Head title="Contas Bancárias" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Contas Bancárias
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de contas bancárias da empresa.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="toUrl(bankAccountsCreate)">
                        <PhPlus class="size-4" weight="bold" />
                        Nova Conta
                    </Link>
                </Button>
            </div>

            <DataTable
                :paginated="bankAccounts"
                :columns="columns"
                :filters="filters"
                :route-fn="bankAccountsIndex"
                search-placeholder="Pesquisar contas..."
            />
        </div>
    </AppLayout>
</template>
