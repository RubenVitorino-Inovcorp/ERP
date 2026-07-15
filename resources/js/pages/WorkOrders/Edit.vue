<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PhArrowLeft, PhCheck } from '@phosphor-icons/vue'
import { computed } from 'vue'
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
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/layouts/AppLayout.vue'
import { update as workOrdersUpdate } from '@/routes/work-orders'

const props = defineProps<{
    workOrder: any
    entities: any[]
    orders: any[]
    users: any[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ordens de Trabalho', href: '/ordens-trabalho' },
    { title: `Editar ${props.workOrder.number}`, href: '#' },
]

const form = useForm({
    entity_id: String(props.workOrder.entity_id),
    order_id: props.workOrder.order_id ? String(props.workOrder.order_id) : 'none',
    description: props.workOrder.description || '',
    priority: props.workOrder.priority || 'normal',
    status: props.workOrder.status || 'pendente',
    expected_duration: props.workOrder.expected_duration ?? '',
    actual_duration: props.workOrder.actual_duration ?? '',
    notes: props.workOrder.notes || '',
    technicians: props.workOrder.users ? props.workOrder.users.map((u: any) => String(u.id)) : [],
})

const filteredOrders = computed(() => {
    if (!form.entity_id) return props.orders
    return props.orders.filter(o => o.entity_id == form.entity_id)
})

const toggleTechnician = (userId: string) => {
    const idx = form.technicians.indexOf(userId)
    if (idx > -1) {
        form.technicians.splice(idx, 1)
    } else {
        form.technicians.push(userId)
    }
}

function submit() {
    form.put(workOrdersUpdate(props.workOrder.id).url, {
        onSuccess: () => {},
    })
}
</script>

<template>
    <Head :title="`Editar ${workOrder.number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar {{ workOrder.number }}</h1>
                <p class="text-sm text-muted-foreground">Atualize os dados da ordem de trabalho.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Número (readonly) -->
                    <FormField name="number">
                        <FormItem>
                            <FormLabel>Número</FormLabel>
                            <FormControl>
                                <Input :model-value="workOrder.number" disabled class="bg-muted/50" />
                            </FormControl>
                        </FormItem>
                    </FormField>

                    <!-- Cliente -->
                    <FormField name="entity_id">
                        <FormItem>
                            <FormLabel>Cliente <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.entity_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.entity_id }">
                                        <SelectValue placeholder="Selecione um cliente..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="entity in entities" :key="entity.id" :value="String(entity.id)">
                                            {{ entity.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.entity_id" class="text-xs text-rose-500">{{ form.errors.entity_id }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <!-- Encomenda Associada -->
                <FormField name="order_id">
                    <FormItem>
                        <FormLabel>Encomenda Associada (Opcional)</FormLabel>
                        <FormControl>
                            <Select v-model="form.order_id">
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

                <!-- Descrição do Trabalho -->
                <FormField name="description">
                    <FormItem>
                        <FormLabel>Descrição do Trabalho <span class="text-destructive">*</span></FormLabel>
                        <FormControl>
                            <Textarea v-model="form.description" placeholder="Descreva o trabalho a realizar..." rows="4" :class="{ 'border-destructive': form.errors.description }" />
                        </FormControl>
                        <FormMessage v-if="form.errors.description" class="text-xs text-rose-500">{{ form.errors.description }}</FormMessage>
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Prioridade -->
                    <FormField name="priority">
                        <FormItem>
                            <FormLabel>Prioridade <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.priority">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.priority }">
                                        <SelectValue placeholder="Selecione..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="baixa">Baixa</SelectItem>
                                        <SelectItem value="normal">Normal</SelectItem>
                                        <SelectItem value="alta">Alta</SelectItem>
                                        <SelectItem value="urgente">Urgente</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.priority" class="text-xs text-rose-500">{{ form.errors.priority }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Estado -->
                    <FormField name="status">
                        <FormItem>
                            <FormLabel>Estado <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.status">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                                        <SelectValue placeholder="Selecione..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pendente">Pendente</SelectItem>
                                        <SelectItem value="em_curso">Em Curso</SelectItem>
                                        <SelectItem value="concluida">Concluída</SelectItem>
                                        <SelectItem value="cancelada">Cancelada</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.status" class="text-xs text-rose-500">{{ form.errors.status }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tempo Previsto -->
                    <FormField name="expected_duration">
                        <FormItem>
                            <FormLabel>Tempo Previsto (min)</FormLabel>
                            <FormControl>
                                <Input type="number" min="0" v-model="form.expected_duration" placeholder="Ex: 120" />
                            </FormControl>
                            <FormMessage v-if="form.errors.expected_duration" class="text-xs text-rose-500">{{ form.errors.expected_duration }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Tempo Gasto -->
                    <FormField name="actual_duration">
                        <FormItem>
                            <FormLabel>Tempo Gasto (min)</FormLabel>
                            <FormControl>
                                <Input type="number" min="0" v-model="form.actual_duration" placeholder="Ex: 90" />
                            </FormControl>
                            <FormMessage v-if="form.errors.actual_duration" class="text-xs text-rose-500">{{ form.errors.actual_duration }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <!-- Técnicos -->
                <div class="space-y-3">
                    <Label class="text-sm font-medium">Equipa Técnica</Label>
                    <p class="text-xs text-muted-foreground -mt-1">Selecione os técnicos que irão realizar este trabalho.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        <label
                            v-for="user in users"
                            :key="user.id"
                            class="flex items-center gap-2 rounded-lg border p-3 cursor-pointer transition-colors"
                            :class="form.technicians.includes(String(user.id)) ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'"
                        >
                            <Checkbox
                                :model-value="form.technicians.includes(String(user.id))"
                                @update:model-value="toggleTechnician(String(user.id))"
                            />
                            <span class="text-sm">{{ user.name }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.technicians" class="text-xs text-rose-500">{{ form.errors.technicians }}</p>
                </div>

                <!-- Notas / Relatório Técnico -->
                <FormField name="notes">
                    <FormItem>
                        <FormLabel>Notas / Relatório Técnico</FormLabel>
                        <FormControl>
                            <Textarea v-model="form.notes" placeholder="Observações adicionais ou relatório da intervenção..." rows="4" />
                        </FormControl>
                    </FormItem>
                </FormField>

                <!-- Evento do Calendário associado -->
                <div v-if="workOrder.calendar_event" class="rounded-lg border bg-muted/30 p-4 space-y-1">
                    <p class="text-sm font-medium text-foreground">📅 Evento de Calendário Associado</p>
                    <p class="text-xs text-muted-foreground">
                        Data: {{ workOrder.calendar_event.date }} às {{ workOrder.calendar_event.time }}
                    </p>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link href="/ordens-trabalho" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhCheck class="size-4" />
                        <span>Guardar Alterações</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
