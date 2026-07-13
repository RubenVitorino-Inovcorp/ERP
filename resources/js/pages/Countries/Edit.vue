<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PhArrowLeft, PhCheck, PhArrowClockwise } from '@phosphor-icons/vue';

const props = defineProps<{
    country: {
        id: number;
        name: string;
        code: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Países', href: '/configuracoes/paises' },
    { title: 'Editar País', href: `/configuracoes/paises/${props.country.id}/editar` },
];

const form = useForm({
    name: props.country.name,
    code: props.country.code,
});

function submit() {
    form.put(route('countries.update', props.country.id));
}
</script>

<template>
    <Head title="Editar País" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar País</h1>
                <p class="text-sm text-muted-foreground">Atualize as informações do país ({{ country.name }}).</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome do País</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: Portugal, Espanha, França, etc."
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="code" class="text-sm font-medium">Código do País</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="Ex: PT, ES, FR, etc."
                            required
                        />
                        <p v-if="form.errors.code" class="text-xs text-rose-500">{{ form.errors.code }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="route('countries.index')" class="gap-1.5">
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
