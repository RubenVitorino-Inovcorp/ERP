<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PhArrowLeft, PhCheck, PhCalendarPlus } from '@phosphor-icons/vue'
import { computed, ref, watch } from 'vue'
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
import { store as workOrdersStore } from '@/routes/work-orders'

const props = defineProps<{
    entities: any[]
    orders: any[]
    users: any[]
    calendarTypes: any[]
    calendarActions: any[]
    initialOrderId?: string | null
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ordens de Trabalho', href: '/ordens-trabalho' },
    { title: 'Nova Ordem de Trabalho', href: '/ordens-trabalho/create' },
]

const form = useForm({
    entity_id: '',
    order_id: props.initialOrderId ? String(props.initialOrderId) : '',
    description: '',
    priority: 'normal',
    status: 'pendente',
    expected_duration: '' as string | number,
    actual_duration: '' as string | number,
    notes: '',
    technicians: [] as string[],
    create_calendar_event: false,
    date: '',
    time: '',
    calendar_type_id: '',
    calendar_action_id: '',
})

// Pré-preencher entity_id quando selecionada uma encomenda
const filteredOrders = computed(() => {
    if (!form.entity_id) return props.orders
    return props.orders.filter(o => o.entity_id == form.entity_id)
})

// Quando muda a encomenda, preenche o cliente automaticamente
watch(() => form.order_id, (newOrderId) => {
    if (newOrderId && newOrderId !== 'none') {
        const order = props.orders.find(o => String(o.id) === newOrderId)
        if (order && !form.entity_id) {
            form.entity_id = String(order.entity_id)
        }
    }
})

// Se iniciar com order_id (vindo da encomenda), preencher cliente
if (props.initialOrderId) {
    const order = props.orders.find(o => String(o.id) === String(props.initialOrderId))
    if (order) {
        form.entity_id = String(order.entity_id)
    }
}

const toggleTechnician = (userId: string) => {
    const idx = form.technicians.indexOf(userId)
    if (idx > -1) {
        form.technicians.splice(idx, 1)
    } else {
        form.technicians.push(userId)
    }
}

function submit() {
    form.post(workOrdersStore().url, {
        onSuccess: () => {},
    })
}
</script>

<template>
    <Head title="Nova Ordem de Trabalho" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Nova Ordem de Trabalho</h1>
                <p class="text-sm text-muted-foreground">Preencha os dados para criar uma nova ordem de trabalho.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>

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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                <!-- Notas -->
                <FormField name="notes">
                    <FormItem>
                        <FormLabel>Notas / Relatório Técnico</FormLabel>
                        <FormControl>
                            <Textarea v-model="form.notes" placeholder="Observações adicionais..." rows="3" />
                        </FormControl>
                    </FormItem>
                </FormField>

                <!-- Agendar no Calendário -->
                <div class="space-y-4 rounded-lg border p-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <Checkbox v-model="form.create_calendar_event" />
                        <PhCalendarPlus class="size-4 text-muted-foreground" />
                        <span class="text-sm font-medium">Agendar no Calendário</span>
                    </label>

                    <div v-if="form.create_calendar_event" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <FormField name="date">
                            <FormItem>
                                <FormLabel>Data <span class="text-destructive">*</span></FormLabel>
                                <FormControl>
                                    <Input type="date" v-model="form.date" :class="{ 'border-destructive': form.errors.date }" />
                                </FormControl>
                                <FormMessage v-if="form.errors.date" class="text-xs text-rose-500">{{ form.errors.date }}</FormMessage>
                            </FormItem>
                        </FormField>

                        <FormField name="time">
                            <FormItem>
                                <FormLabel>Hora <span class="text-destructive">*</span></FormLabel>
                                <FormControl>
                                    <Input type="time" v-model="form.time" :class="{ 'border-destructive': form.errors.time }" />
                                </FormControl>
                                <FormMessage v-if="form.errors.time" class="text-xs text-rose-500">{{ form.errors.time }}</FormMessage>
                            </FormItem>
                        </FormField>

                        <FormField name="calendar_type_id">
                            <FormItem>
                                <FormLabel>Tipo de Evento <span class="text-destructive">*</span></FormLabel>
                                <FormControl>
                                    <Select v-model="form.calendar_type_id">
                                        <SelectTrigger :class="{ 'border-destructive': form.errors.calendar_type_id }">
                                            <SelectValue placeholder="Selecione..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="type in calendarTypes" :key="type.id" :value="String(type.id)">
                                                {{ type.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                                <FormMessage v-if="form.errors.calendar_type_id" class="text-xs text-rose-500">{{ form.errors.calendar_type_id }}</FormMessage>
                            </FormItem>
                        </FormField>

                        <FormField name="calendar_action_id">
                            <FormItem>
                                <FormLabel>Ação do Evento <span class="text-destructive">*</span></FormLabel>
                                <FormControl>
                                    <Select v-model="form.calendar_action_id">
                                        <SelectTrigger :class="{ 'border-destructive': form.errors.calendar_action_id }">
                                            <SelectValue placeholder="Selecione..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="action in calendarActions" :key="action.id" :value="String(action.id)">
                                                {{ action.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                                <FormMessage v-if="form.errors.calendar_action_id" class="text-xs text-rose-500">{{ form.errors.calendar_action_id }}</FormMessage>
                            </FormItem>
                        </FormField>
                    </div>
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
                        <span>Criar Ordem de Trabalho</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
