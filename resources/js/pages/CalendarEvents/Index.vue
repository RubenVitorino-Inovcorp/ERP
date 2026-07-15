<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { PhPlus } from '@phosphor-icons/vue'
import { ref, watch } from 'vue'
import CalendarEventForm from '@/components/CalendarEventForm.vue'
import CalendarWidget from '@/components/CalendarWidget.vue'
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
    events: any[]
    users: any[]
    entities: any[]
    calendarTypes: any[]
    calendarActions: any[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Calendário', href: '/calendario' },
]

// Fetch initial params from URL if any
const params = new URLSearchParams(window.location.search)
const filterUser = ref(params.get('user_id') || 'todos')
const filterEntity = ref(params.get('entity_id') || 'todas')

// Auto-submit filter changes
watch([filterUser, filterEntity], ([user, entity]) => {
    router.get((window as any).route('calendar-events.index'), {
        user_id: user === 'todos' ? null : user,
        entity_id: entity === 'todas' ? null : entity,
    }, { preserveState: true, replace: true })
})

// Gestão do Painel Lateral (Sheet)
const isSheetOpen = ref(false)
const selectedEvent = ref<any>(null)

function handleDateClick(dateStr: string) {
    selectedEvent.value = {
        date: dateStr,
        time: '09:00', // Pre-fill default
        duration: 60,
    }
    isSheetOpen.value = true
}

function handleEventClick(eventId: number) {
    // Procura o evento nos props originais recebidos do backend
    const event = props.events.find((e: any) => e.id == eventId)

    if (event) {
        selectedEvent.value = {
            id: event.id,
            date: event.raw?.date,
            time: event.raw?.time,
            duration: event.raw?.duration,
            entity_id: event.raw?.entity_id,
            calendar_type_id: event.raw?.calendar_type_id,
            calendar_action_id: event.raw?.calendar_action_id,
            description: event.raw?.description,
            status: event.raw?.status,
            partilha: event.raw?.partilha || [],
            conhecimento: event.raw?.conhecimento || [],
        }
        isSheetOpen.value = true
    }
}

function openCreateForm() {
    selectedEvent.value = null
    isSheetOpen.value = true
}

function onFormSaved() {
    isSheetOpen.value = false
}
</script>

<template>
    <Head title="Calendário" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col p-4 md:p-6 gap-6">
            <!-- Cabeçalho da Página e Ações -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Calendário
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de eventos e agendamentos.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button class="gap-2" @click="openCreateForm">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Evento
                    </Button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex items-center gap-4 bg-white dark:bg-white/[0.02] border p-4 rounded-xl shadow-sm">
                <div class="w-full sm:w-64 space-y-1.5">
                    <label class="text-xs font-medium text-muted-foreground">Filtrar por Utilizador</label>
                    <Select v-model="filterUser">
                        <SelectTrigger>
                            <SelectValue placeholder="Todos os Utilizadores" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">Todos</SelectItem>
                            <SelectItem v-for="user in users" :key="user.id" :value="user.id.toString()">
                                {{ user.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                
                <div class="w-full sm:w-64 space-y-1.5">
                    <label class="text-xs font-medium text-muted-foreground">Filtrar por Entidade</label>
                    <Select v-model="filterEntity">
                        <SelectTrigger>
                            <SelectValue placeholder="Todas as Entidades" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todas">Todas</SelectItem>
                            <SelectItem v-for="ent in entities" :key="ent.id" :value="ent.id.toString()">
                                {{ ent.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Calendário Widget -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-4 w-full flex-1 min-h-[600px]">
                <CalendarWidget 
                    :events="events" 
                    @dateClick="handleDateClick"
                    @eventClick="handleEventClick"
                />
            </div>
        </div>

        <!-- Sheet para Criação e Edição de Eventos -->
        <Sheet :open="isSheetOpen" @update:open="isSheetOpen = $event">
            <SheetContent class="w-full sm:max-w-md overflow-y-auto p-6">
                <SheetHeader>
                    <SheetTitle>{{ selectedEvent?.id ? 'Editar Evento' : 'Novo Evento' }}</SheetTitle>
                    <SheetDescription>
                        Preencha os detalhes do evento. Clique em Guardar quando terminar.
                    </SheetDescription>
                </SheetHeader>
                
                <CalendarEventForm 
                    v-if="isSheetOpen"
                    :event="selectedEvent"
                    :users="users"
                    :entities="entities"
                    :types="calendarTypes"
                    :actions="calendarActions"
                    @saved="onFormSaved"
                    @cancelled="isSheetOpen = false"
                />
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>