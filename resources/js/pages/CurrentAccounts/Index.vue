<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PhEye, PhPlus } from '@phosphor-icons/vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { h, ref, computed } from 'vue'
import { toast } from 'vue-sonner'
import DataTable from '@/components/DataTable.vue'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import AppLayout from '@/layouts/AppLayout.vue'
import { show as currentAccountShow, store as currentAccountStore } from '@/routes/conta-corrente'

const props = defineProps<{
    clients: any
    allClients: any[]
    filters: any
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '#' },
    { title: 'Conta Corrente Clientes', href: '/financeiro/conta-corrente' },
]

const showAddModal = ref(false)

const form = useForm({
    client_id: '',
    date: new Date().toISOString().split('T')[0],
    document_type: '',
    document_number: '',
    description: '',
    movement_type: 'debit',
    amount: '',
    attachment: null as File | null,
})

function submitEntry() {
    if (!form.client_id) {
        toast.error('Por favor, selecione um cliente.')

        return
    }

    form.post(currentAccountStore(Number(form.client_id)).url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showAddModal.value = false
            form.reset()
            toast.success('Movimento registado com sucesso.')
        },
        onError: () => {
            toast.error('Erro ao registar movimento. Verifique os campos.')
        }
    })
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement

    if (target.files && target.files.length > 0) {
        form.attachment = target.files[0]
    } else {
        form.attachment = null
    }
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'nif',
        header: 'NIF',
        enableSorting: true,
    },
    {
        accessorKey: 'name',
        header: 'Nome do Cliente',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-semibold' }, row.getValue('name')),
    },
    {
        accessorKey: 'email',
        header: 'E-mail',
        enableSorting: true,
        cell: ({ row }) => {
            const email = row.getValue('email') as string | null

            return email ? h('a', { href: `mailto:${email}`, class: 'text-primary hover:underline' }, email) : '-'
        },
    },
    {
        accessorKey: 'phone',
        header: 'Telefone',
        enableSorting: true,
    },
    {
        accessorKey: 'current_balance',
        header: 'Saldo Pendente',
        enableSorting: false, // Calculated on the fly
        cell: ({ row }) => {
            const balance = Number(row.getValue('current_balance')) || 0
            const isNegative = balance < 0
            const isPositive = balance > 0
            
            return h(
                'span',
                {
                    class: [
                        'font-medium text-right block',
                        isPositive ? 'text-destructive' : (isNegative ? 'text-emerald-600' : 'text-muted-foreground')
                    ]
                },
                `${balance.toFixed(2)} €`
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
                        href: currentAccountShow(item.id).url,
                        title: 'Ver Extrato',
                        class: 'text-muted-foreground hover:text-primary transition-colors',
                    },
                    () => h(PhEye, { class: 'size-4' })
                ),
            ])
        },
    },
]
</script>

<template>
    <Head title="Conta Corrente Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Conta Corrente Clientes
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Extrato financeiro e controlo de saldos pendentes.
                    </p>
                </div>
                
                <Dialog v-model:open="showAddModal">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <PhPlus class="size-4" />
                            Registar Movimento
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[500px]">
                        <DialogHeader>
                            <DialogTitle>Registar Novo Movimento</DialogTitle>
                            <DialogDescription>
                                Adicione um movimento financeiro a um cliente diretamente.
                            </DialogDescription>
                        </DialogHeader>

                        <form id="addEntryGlobalForm" @submit.prevent="submitEntry" class="grid gap-4 py-4">
                            <!-- Cliente -->
                            <FormField name="client_id">
                                <FormItem>
                                    <FormLabel>Cliente <span class="text-destructive">*</span></FormLabel>
                                    <FormControl>
                                        <Select v-model="form.client_id">
                                            <SelectTrigger :class="{ 'border-destructive': form.errors.client_id }">
                                                <SelectValue placeholder="Selecione um cliente..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="client in allClients" :key="client.id" :value="String(client.id)">
                                                    {{ client.name }} ({{ client.nif }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
                                    <FormMessage v-if="form.errors.client_id">{{ form.errors.client_id }}</FormMessage>
                                </FormItem>
                            </FormField>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Data -->
                                <FormField name="date">
                                    <FormItem>
                                        <FormLabel>Data <span class="text-destructive">*</span></FormLabel>
                                        <FormControl>
                                            <Input type="date" v-model="form.date" :class="{ 'border-destructive': form.errors.date }" />
                                        </FormControl>
                                        <FormMessage v-if="form.errors.date">{{ form.errors.date }}</FormMessage>
                                    </FormItem>
                                </FormField>

                                <!-- Tipo Documento -->
                                <FormField name="document_type">
                                    <FormItem>
                                        <FormLabel>Tipo de Documento <span class="text-destructive">*</span></FormLabel>
                                        <FormControl>
                                            <Select v-model="form.document_type">
                                                <SelectTrigger :class="{ 'border-destructive': form.errors.document_type }">
                                                    <SelectValue placeholder="Selecione..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="Fatura">Fatura</SelectItem>
                                                    <SelectItem value="Fatura-Recibo">Fatura-Recibo</SelectItem>
                                                    <SelectItem value="Recibo">Recibo</SelectItem>
                                                    <SelectItem value="Nota de Crédito">Nota de Crédito</SelectItem>
                                                    <SelectItem value="Nota de Débito">Nota de Débito</SelectItem>
                                                    <SelectItem value="Saldo Inicial">Saldo Inicial</SelectItem>
                                                    <SelectItem value="Pagamento">Pagamento Livre</SelectItem>
                                                    <SelectItem value="Outro">Outro</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </FormControl>
                                        <FormMessage v-if="form.errors.document_type">{{ form.errors.document_type }}</FormMessage>
                                    </FormItem>
                                </FormField>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Nº Documento -->
                                <FormField name="document_number">
                                    <FormItem>
                                        <FormLabel>Nº Documento</FormLabel>
                                        <FormControl>
                                            <Input v-model="form.document_number" placeholder="Ex: FT 2026/1" :class="{ 'border-destructive': form.errors.document_number }" />
                                        </FormControl>
                                        <FormMessage v-if="form.errors.document_number">{{ form.errors.document_number }}</FormMessage>
                                    </FormItem>
                                </FormField>

                                <!-- Valor -->
                                <FormField name="amount">
                                    <FormItem>
                                        <FormLabel>Valor (€) <span class="text-destructive">*</span></FormLabel>
                                        <FormControl>
                                            <Input type="number" step="0.01" v-model="form.amount" placeholder="0.00" :class="{ 'border-destructive': form.errors.amount }" />
                                        </FormControl>
                                        <FormMessage v-if="form.errors.amount">{{ form.errors.amount }}</FormMessage>
                                    </FormItem>
                                </FormField>
                            </div>

                            <!-- Tipo de Movimento -->
                            <FormField name="movement_type">
                                <FormItem>
                                    <FormLabel>Natureza do Movimento <span class="text-destructive">*</span></FormLabel>
                                    <div class="grid grid-cols-2 gap-4">
                                        <label class="flex cursor-pointer items-center justify-center rounded-lg border p-3 hover:bg-muted/50" :class="form.movement_type === 'debit' ? 'border-destructive bg-destructive/5' : ''">
                                            <input type="radio" v-model="form.movement_type" value="debit" class="sr-only" />
                                            <div class="text-center">
                                                <div class="font-medium" :class="form.movement_type === 'debit' ? 'text-destructive' : 'text-foreground'">Débito</div>
                                                <div class="text-xs text-muted-foreground mt-1">Aumenta a dívida</div>
                                            </div>
                                        </label>
                                        <label class="flex cursor-pointer items-center justify-center rounded-lg border p-3 hover:bg-muted/50" :class="form.movement_type === 'credit' ? 'border-emerald-500 bg-emerald-500/5' : ''">
                                            <input type="radio" v-model="form.movement_type" value="credit" class="sr-only" />
                                            <div class="text-center">
                                                <div class="font-medium" :class="form.movement_type === 'credit' ? 'text-emerald-600' : 'text-foreground'">Crédito</div>
                                                <div class="text-xs text-muted-foreground mt-1">Diminui a dívida</div>
                                            </div>
                                        </label>
                                    </div>
                                    <FormMessage v-if="form.errors.movement_type">{{ form.errors.movement_type }}</FormMessage>
                                </FormItem>
                            </FormField>

                            <!-- Descrição -->
                            <FormField name="description">
                                <FormItem>
                                    <FormLabel>Descrição</FormLabel>
                                    <FormControl>
                                        <Textarea v-model="form.description" rows="2" placeholder="Observações adicionais..." />
                                    </FormControl>
                                    <FormMessage v-if="form.errors.description">{{ form.errors.description }}</FormMessage>
                                </FormItem>
                            </FormField>

                            <!-- Anexo -->
                            <FormField name="attachment">
                                <FormItem>
                                    <FormLabel>Comprovativo / Anexo</FormLabel>
                                    <FormControl>
                                        <Input type="file" @change="handleFileChange" accept=".pdf,.jpg,.jpeg,.png" />
                                    </FormControl>
                                    <FormMessage v-if="form.errors.attachment">{{ form.errors.attachment }}</FormMessage>
                                    <p class="text-[10px] text-muted-foreground mt-1">Formatos aceites: PDF, JPG, PNG (Max 5MB)</p>
                                </FormItem>
                            </FormField>
                        </form>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="showAddModal = false">Cancelar</Button>
                            <Button type="submit" form="addEntryGlobalForm" :disabled="form.processing">Registar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <DataTable
                :columns="columns"
                :paginated="clients"
                :filters="filters"
                search-placeholder="Pesquisar cliente, NIF ou e-mail..."
            />
        </div>
    </AppLayout>
</template>
