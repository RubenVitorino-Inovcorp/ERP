<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { PhFilePdf, PhPencilSimple, PhTrash, PhCheckCircle } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { format } from 'date-fns'
import { pt } from 'date-fns/locale'
import { h, ref } from 'vue'
import { toast } from 'vue-sonner'
import DataTable from '@/components/DataTable.vue'
import DeleteConfirmation from '@/components/DeleteConfirmation.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { edit as supplierInvoicesEdit, destroy as supplierInvoicesDestroy, create as supplierInvoicesCreate, pay as supplierInvoicesPay } from '@/routes/supplier-invoices'

const props = defineProps<{
    invoices: any
    filters: any
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '#' },
    { title: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedores' },
]

const deleteInvoice = (id: number) => {
    router.delete(supplierInvoicesDestroy(id).url, {
        onSuccess: () => {
            // Toast is handled by backend redirect with success message, but we can rely on it if flash is set
        },
    })
}

// Payment Modal State
const showPayModal = ref(false)
const selectedInvoiceId = ref<number | null>(null)

const payForm = useForm({
    proof_of_payment: null as File | null
})

const openPayModal = (id: number) => {
    selectedInvoiceId.value = id
    showPayModal.value = true
}

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement

    if (target.files && target.files.length > 0) {
        payForm.proof_of_payment = target.files[0]
    } else {
        payForm.proof_of_payment = null
    }
}

const submitPayment = () => {
    if (!selectedInvoiceId.value) {
return
}

    payForm.post(supplierInvoicesPay(selectedInvoiceId.value).url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showPayModal.value = false
            payForm.reset()
            toast.success('Fatura marcada como paga com sucesso!')
        },
        onError: () => {
            toast.error('Ocorreu um erro ao registar o pagamento.')
        }
    })
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'invoice_date',
        header: 'Data Fatura',
        enableSorting: true,
        cell: ({ row }) => format(new Date(row.getValue('invoice_date')), 'dd MMM yyyy', { locale: pt }),
    },
    {
        accessorKey: 'number',
        header: 'Número',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('number')),
    },
    {
        accessorKey: 'entity.name',
        id: 'fornecedor',
        header: 'Fornecedor',
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
        accessorKey: 'total_value',
        header: 'Valor Total',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-semibold' }, `${Number(row.getValue('total_value')).toFixed(2)} €`),
    },
    {
        accessorKey: 'status',
        header: 'Estado',
        enableSorting: true,
        cell: ({ row }) => {
            const status = row.getValue('status') as string
            const isPaga = status === 'paga'
            
            return h(
                Badge,
                { variant: isPaga ? 'default' : 'destructive', class: isPaga ? 'bg-emerald-500 hover:bg-emerald-600' : '' },
                () => (isPaga ? 'Paga' : 'Pendente')
            )
        },
    },
    {
        id: 'document',
        header: 'Documento',
        enableSorting: false,
        cell: ({ row }) => {
            const path = row.original.document_path

            return path 
                ? h('a', { href: `/storage/${path}`, target: '_blank', class: 'text-primary hover:text-primary/80 transition-colors flex items-center gap-1', title: 'Ver Fatura' }, [
                    h(PhFilePdf, { class: 'size-4' }),
                    h('span', { class: 'text-xs uppercase' }, 'PDF')
                ])
                : '-'
        }
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const item = row.original

            const actions = []
            
            if (item.status === 'pendente') {
                actions.push(
                    h(
                        'button',
                        {
                            onClick: () => openPayModal(item.id),
                            title: 'Marcar como Paga',
                            class: 'text-emerald-600 hover:text-emerald-700 transition-colors',
                        },
                        [h(PhCheckCircle, { class: 'size-4' })]
                    )
                )
            }

            actions.push(
                h(
                    Link,
                    {
                        href: supplierInvoicesEdit(item.id).url,
                        title: 'Editar',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhPencilSimple, { class: 'size-4' })
                )
            )

            actions.push(
                h(DeleteConfirmation, {
                    onConfirm: () => deleteInvoice(item.id),
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
            )

            return h('div', { class: 'flex items-center justify-end gap-3' }, actions)
        },
    },
]
</script>

<template>
    <Head title="Faturas Fornecedores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Faturas Fornecedores
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de faturas e pagamentos a fornecedores.
                    </p>
                </div>
                <Link :href="supplierInvoicesCreate().url">
                    <Button>Registar Fatura</Button>
                </Link>
            </div>

            <DataTable
                :columns="columns"
                :paginated="invoices"
                :filters="filters"
                search-placeholder="Pesquisar número da fatura..."
            />
            
            <!-- Pay Modal -->
            <Dialog v-model:open="showPayModal">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Marcar como Paga</DialogTitle>
                        <DialogDescription>
                            Pretende enviar o comprovativo ao Fornecedor? Anexe o ficheiro abaixo.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form id="payForm" @submit.prevent="submitPayment" class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label htmlFor="proof_of_payment">Comprovativo de Pagamento (Opcional)</Label>
                            <input 
                                id="proof_of_payment" 
                                type="file" 
                                accept=".pdf,.jpg,.jpeg,.png,.webp"
                                @change="handleFileChange"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <p class="text-[10px] text-muted-foreground">O ficheiro em anexo será enviado diretamente para o email do fornecedor.</p>
                            <p v-if="payForm.errors.proof_of_payment" class="text-xs text-destructive">{{ payForm.errors.proof_of_payment }}</p>
                        </div>
                    </form>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showPayModal = false">Cancelar</Button>
                        <Button type="submit" form="payForm" :disabled="payForm.processing">Confirmar Pagamento</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
