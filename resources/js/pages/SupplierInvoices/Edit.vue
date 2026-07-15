<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PhArrowLeft, PhCheck, PhUploadSimple, PhImage } from '@phosphor-icons/vue'
import { computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import { update as supplierInvoicesUpdate } from '@/routes/supplier-invoices'

import { useDropzone } from 'vue3-dropzone'
import { toast } from 'vue-sonner'

const props = defineProps<{
    invoice: any
    suppliers: any[]
    orders: any[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '#' },
    { title: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedores' },
    { title: 'Editar Fatura', href: '#' },
]

const form = useForm({
    entity_id: String(props.invoice.entity_id),
    order_id: props.invoice.order_id ? String(props.invoice.order_id) : '',
    number: props.invoice.number,
    total_value: props.invoice.total_value,
    invoice_date: props.invoice.invoice_date,
    due_date: props.invoice.due_date,
    document: null as File | null,
    _method: 'PUT',
})

const filteredOrders = computed(() => {
    if (!form.entity_id) return []
    return props.orders.filter(order => order.entity_id == form.entity_id)
})

watch(() => form.entity_id, (newVal, oldVal) => {
    if (oldVal !== '') {
        form.order_id = ''
    }
})

watch(() => form.order_id, (newOrderId, oldVal) => {
    if (newOrderId && newOrderId !== 'none' && oldVal !== '') {
        const order = props.orders.find(o => String(o.id) === newOrderId)
        if (order) {
            form.total_value = order.total_value
            if (order.number) {
                const year = order.order_date ? new Date(order.order_date).getFullYear() : new Date().getFullYear()
                const formattedNumber = String(order.number).padStart(2, '0')
                form.number = `FT ${year}/${formattedNumber}`
            }
            if (order.order_date) {
                form.invoice_date = order.order_date
                form.due_date = order.order_date
            }
        }
    }
})

// vue3-dropzone
function onDrop(acceptedFiles: File[], rejectReasons: any[]) {
    if (rejectReasons.length > 0) {
        toast.error('Ficheiro rejeitado. Verifique o tipo e tamanho do ficheiro.')
        return
    }

    if (acceptedFiles.length > 0) {
        form.document = acceptedFiles[0]
        toast.success('Novo documento selecionado.')
    }
}

const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'],
    maxSize: 5 * 1024 * 1024, // 5MB
    multiple: false,
    maxFiles: 1,
})

function submit() {
    form.post(supplierInvoicesUpdate({ supplierInvoice: props.invoice.id }).url, {
        forceFormData: true,
        onSuccess: () => {
            // Sucesso tratado via toast globais/flash se existir
        }
    })
}
</script>

<template>
    <Head title="Editar Fatura" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar Fatura Fornecedor</h1>
                <p class="text-sm text-muted-foreground">Altere os dados da fatura ou atualize o documento.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <!-- Documento -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium">Substituir Documento (Opcional)</Label>
                    <p class="text-sm text-muted-foreground -mt-1">Carregue um novo PDF ou imagem original para substituir. Deixe em branco para manter o atual.</p>
                    
                    <div class="flex flex-col gap-4 mt-2">
                        <div v-if="invoice.document_path" class="text-sm">
                            <a :href="`/storage/${invoice.document_path}`" target="_blank" class="text-primary hover:underline font-medium inline-flex items-center gap-1.5">
                                Ver documento atual guardado
                            </a>
                        </div>
                        
                        <!-- Dropzone -->
                        <div
                            v-bind="getRootProps()"
                            class="flex-1 cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                            :class="{
                                'border-primary bg-primary/5': isDragActive,
                                'border-border hover:border-primary/50 hover:bg-muted/50': !isDragActive,
                            }"
                        >
                            <input v-bind="getInputProps()" />
                            <div class="flex flex-col items-center gap-2">
                                <div class="rounded-full bg-muted p-3">
                                    <PhUploadSimple v-if="!isDragActive" class="size-6 text-muted-foreground" />
                                    <PhImage v-else class="size-6 text-primary" />
                                </div>
                                <div>
                                    <p v-if="isDragActive" class="text-sm font-medium text-primary">
                                        Largue o ficheiro aqui...
                                    </p>
                                    <p v-else class="text-sm font-medium text-foreground">
                                        Arraste e largue ou <span class="text-primary">clique para selecionar novo ficheiro</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        PDF, JPG, PNG ou WEBP (máx. 5MB)
                                    </p>
                                    <p v-if="form.document" class="text-sm font-medium text-primary mt-2">
                                        Ficheiro selecionado para substituição: {{ form.document.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.document" class="text-xs text-rose-500">{{ form.errors.document }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fornecedor -->
                    <FormField name="entity_id">
                        <FormItem>
                            <FormLabel>Fornecedor <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.entity_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.entity_id }">
                                        <SelectValue placeholder="Selecione um fornecedor..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="String(supplier.id)">
                                            {{ supplier.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.entity_id" class="text-xs text-rose-500">{{ form.errors.entity_id }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Encomenda -->
                    <FormField name="order_id">
                        <FormItem>
                            <FormLabel>Encomenda Associada (Opcional)</FormLabel>
                            <FormControl>
                                <Select v-model="form.order_id" :disabled="!form.entity_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.order_id }">
                                        <SelectValue placeholder="Nenhuma" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">-- Nenhuma --</SelectItem>
                                        <SelectItem v-for="order in filteredOrders" :key="order.id" :value="String(order.id)">
                                            ENC {{ order.number }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.order_id" class="text-xs text-rose-500">{{ form.errors.order_id }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Número da Fatura -->
                    <FormField name="number">
                        <FormItem>
                            <FormLabel>Número da Fatura <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input v-model="form.number" placeholder="Ex: FT 2026/01" :class="{ 'border-destructive': form.errors.number }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.number" class="text-xs text-rose-500">{{ form.errors.number }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Valor Total -->
                    <FormField name="total_value">
                        <FormItem>
                            <FormLabel>Valor Total (€) <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input type="number" step="0.01" min="0" v-model="form.total_value" placeholder="0.00" :class="{ 'border-destructive': form.errors.total_value }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.total_value" class="text-xs text-rose-500">{{ form.errors.total_value }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Data da Fatura -->
                    <FormField name="invoice_date">
                        <FormItem>
                            <FormLabel>Data da Fatura <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input type="date" v-model="form.invoice_date" :class="{ 'border-destructive': form.errors.invoice_date }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.invoice_date" class="text-xs text-rose-500">{{ form.errors.invoice_date }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Data de Vencimento -->
                    <FormField name="due_date">
                        <FormItem>
                            <FormLabel>Data de Vencimento <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input type="date" v-model="form.due_date" :class="{ 'border-destructive': form.errors.due_date }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.due_date" class="text-xs text-rose-500">{{ form.errors.due_date }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link href="/financeiro/faturas-fornecedores" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhCheck class="size-4" />
                        <span>Atualizar Fatura</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
