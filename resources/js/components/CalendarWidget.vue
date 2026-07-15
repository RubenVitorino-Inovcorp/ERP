<script setup lang="ts">
import ptLocale from '@fullcalendar/core/locales/pt'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import timeGridPlugin from '@fullcalendar/timegrid'
import FullCalendar from '@fullcalendar/vue3'
import { ref, watch } from 'vue'

const props = defineProps<{
    events: any[]
}>()

const emit = defineEmits<{
    (e: 'dateClick', date: string): void
    (e: 'eventClick', eventId: number): void
}>()

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    locale: ptLocale,
    events: props.events,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    height: 'auto',
    dateClick: (info: any) => {
        emit('dateClick', info.dateStr)
    },
    eventClick: (info: any) => {
        // Emit the actual ID of the event so the parent can load it
        emit('eventClick', parseInt(info.event.id))
    },
})

// Keep calendar events updated when props change
watch(() => props.events, (newEvents) => {
    calendarOptions.value.events = newEvents
}, { deep: true })
</script>

<template>
    <FullCalendar :options="calendarOptions" />
</template>

<style>
/* Mapeamento das variáveis do FullCalendar para as variáveis do Shadcn e do teu tema */
.fc {
    --fc-border-color: var(--border);
    --fc-button-text-color: var(--foreground);
    --fc-button-bg-color: transparent;
    --fc-button-border-color: var(--border);
    --fc-button-hover-bg-color: var(--accent);
    --fc-button-hover-border-color: var(--border);
    --fc-button-active-bg-color: var(--primary);
    --fc-button-active-border-color: var(--primary);
    --fc-button-active-text-color: var(--primary-foreground);
    --fc-today-bg-color: var(--accent);
    --fc-neutral-bg-color: var(--muted);
    --fc-list-event-hover-bg-color: var(--accent);
    --fc-page-bg-color: var(--background);
}

.fc-theme-standard td, .fc-theme-standard th {
    border-color: var(--border);
}

.fc-event {
    background-color: var(--primary) !important;
    color: var(--primary-foreground) !important;
    border: none !important;
    border-radius: 0.375rem !important;
    padding: 0.125rem 0.375rem !important;
    font-size: 0.75rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
}

.fc-daygrid-event-dot {
    display: none !important;
}

.fc .fc-button {
    font-weight: 500 !important;
    border-radius: 0.375rem !important;
    transition: background-color 0.2s, border-color 0.2s, color 0.2s !important;
    font-size: 0.875rem !important;
    padding: 0.375rem 0.75rem !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active, 
.fc .fc-button-primary:not(:disabled):active {
    background-color: var(--primary) !important;
    color: var(--primary-foreground) !important;
    border-color: var(--primary) !important;
}

.fc .fc-button-primary:not(:disabled):hover {
    background-color: var(--accent) !important;
    color: var(--accent-foreground) !important;
    border-color: var(--border) !important;
}

.fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    letter-spacing: -0.025em !important;
}
</style>
