<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { PhArrowLeft, PhPlus, PhTrash, PhFilePdf, PhFile, PhDownloadSimple } from '@phosphor-icons/vue'
import { format } from 'date-fns'
import { pt } from 'date-fns/locale'
import { h, ref } from 'vue'
import { toast } from 'vue-sonner'
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
import { destroy as currentAccountDestroy, store as currentAccountStore } from '@/routes/conta-corrente'

const props = defineProps<{
    client: any
    entries: any[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Conta Corrente Clientes', href: '/financeiro/conta-corrente' },
    { title: props.client.name, href: '#' },
]

const showAddModal = ref(false)

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    document_type: '',
    document_number: '',
    description: '',
    movement_type: 'debit',
    amount: '',
    attachment: null as File | null,
})

function submitEntry() {
    form.post(currentAccountStore(props.client.id).url, {
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

function deleteEntry(id: number) {
    router.delete(currentAccountDestroy(id).url, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Movimento eliminado com sucesso.')
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar movimento.')
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
</script>

<template>
    <Head :title="`Conta Corrente - ${client.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            
            <!-- Header and Navigation -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Extrato: {{ client.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        NIF: {{ client.nif }}
                    </p>
                </div>
                <Link href="/financeiro/conta-corrente">
                    <Button variant="outline" class="gap-2">
                        <PhArrowLeft class="size-4" />
                        Voltar
                    </Button>
                </Link>
            </div>

            <!-- Resumo de Saldos -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <p class="text-sm font-medium text-muted-foreground">Total Faturado (Débitos)</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">{{ Number(client.total_debit).toFixed(2) }} €</p>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <p class="text-sm font-medium text-muted-foreground">Total Pago (Créditos)</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">{{ Number(client.total_credit).toFixed(2) }} €</p>
                </div>
                <div class="rounded-xl border p-6 shadow-sm" :class="client.current_balance > 0 ? 'bg-destructive/10 border-destructive/20' : (client.current_balance < 0 ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-card')">
                    <p class="text-sm font-medium" :class="client.current_balance > 0 ? 'text-destructive' : (client.current_balance < 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-muted-foreground')">
                        Saldo Pendente
                    </p>
                    <p class="mt-2 text-3xl font-bold" :class="client.current_balance > 0 ? 'text-destructive' : (client.current_balance < 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-foreground')">
                        {{ Number(client.current_balance).toFixed(2) }} €
                    </p>
                </div>
            </div>

            <!-- Tabela de Movimentos -->
            <div class="rounded-xl border bg-card shadow-sm flex flex-col">
                <div class="flex items-center justify-between border-b p-4 sm:p-6">
                    <h2 class="text-lg font-semibold">Movimentos</h2>
                    
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
                                    Adicione uma fatura, recibo ou outro documento à conta corrente deste cliente.
                                </DialogDescription>
                            </DialogHeader>

                            <form id="addEntryForm" @submit.prevent="submitEntry" class="grid gap-4 py-4">
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
                                <Button type="submit" form="addEntryForm" :disabled="form.processing">Registar</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="p-4 font-semibold">Data</th>
                                <th class="p-4 font-semibold">Documento</th>
                                <th class="p-4 font-semibold">Descrição</th>
                                <th class="p-4 font-semibold text-right text-destructive">Débito</th>
                                <th class="p-4 font-semibold text-right text-emerald-600">Crédito</th>
                                <th class="p-4 font-semibold text-right">Saldo Contínuo</th>
                                <th class="p-4 w-24"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="entry in entries" :key="entry.id" class="hover:bg-muted/30 transition-colors">
                                <td class="p-4 whitespace-nowrap">{{ format(new Date(entry.date), 'dd MMM yyyy', { locale: pt }) }}</td>
                                <td class="p-4">
                                    <div class="font-medium text-foreground">{{ entry.document_type }}</div>
                                    <div class="text-xs text-muted-foreground" v-if="entry.document_number">{{ entry.document_number }}</div>
                                </td>
                                <td class="p-4 text-muted-foreground max-w-[200px] truncate" :title="entry.description || ''">
                                    {{ entry.description || '-' }}
                                </td>
                                <td class="p-4 text-right font-medium">
                                    <span v-if="entry.movement_type === 'debit'" class="text-destructive">{{ Number(entry.amount).toFixed(2) }} €</span>
                                    <span v-else class="text-muted-foreground/30">-</span>
                                </td>
                                <td class="p-4 text-right font-medium">
                                    <span v-if="entry.movement_type === 'credit'" class="text-emerald-600">{{ Number(entry.amount).toFixed(2) }} €</span>
                                    <span v-else class="text-muted-foreground/30">-</span>
                                </td>
                                <td class="p-4 text-right font-bold" :class="entry.running_balance > 0 ? 'text-destructive' : (entry.running_balance < 0 ? 'text-emerald-600' : 'text-foreground')">
                                    {{ Number(entry.running_balance).toFixed(2) }} €
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a v-if="entry.attachment_path" :href="`/storage/${entry.attachment_path}`" target="_blank" class="text-muted-foreground hover:text-primary transition-colors" title="Ver Anexo">
                                            <PhDownloadSimple class="size-4" />
                                        </a>
                                        <DeleteConfirmation @confirm="deleteEntry(entry.id)">
                                            <template #trigger>
                                                <button class="text-muted-foreground hover:text-destructive transition-colors" title="Eliminar Movimento">
                                                    <PhTrash class="size-4" />
                                                </button>
                                            </template>
                                        </DeleteConfirmation>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="entries.length === 0">
                                <td colspan="7" class="p-8 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <PhFile class="size-8 opacity-20" />
                                        <p>Sem movimentos registados para este cliente.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
