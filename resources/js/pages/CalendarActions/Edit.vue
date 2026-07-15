<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhCheck, PhArrowClockwise } from '@phosphor-icons/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    calendarAction: {
        id: number;
        name: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Agenda', href: '#' },
    { title: 'Ações de Eventos', href: '/agenda/acoes-de-eventos' },
    { title: 'Editar Ação', href: `/agenda/acoes-de-eventos/${props.calendarAction.id}/editar` },
];

const form = useForm({
    name: props.calendarAction.name,
});

function submit() {
    form.put(route('calendar-actions.update', props.calendarAction.id));
}
</script>

<template>
    <Head title="Editar Ação de Evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar Ação de Evento</h1>
                <p class="text-sm text-muted-foreground">Atualize as informações da ação de evento ({{ calendarAction.name }}).</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome da Ação de Evento</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: Ligar, Enviar Email, Visita, Reunião, etc."
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="route('calendar-actions.index')" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                        <PhCheck v-else class="size-4" />
                        <span>Guardar Alterações</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
