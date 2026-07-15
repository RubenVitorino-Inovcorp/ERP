<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhCheck, PhArrowClockwise } from '@phosphor-icons/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Categorias de Documentos', href: '/configuracoes/categorias-documentos' },
    { title: 'Nova Categoria', href: '#' },
];

const form = useForm({
    name: '',
    status: true,
});

function submit() {
    form.post(route('document-categories.store'));
}
</script>

<template>
    <Head title="Nova Categoria de Documento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Nova Categoria de Documento</h1>
                <p class="text-sm text-muted-foreground">Adicione uma nova categoria para classificar documentos no arquivo digital.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome da Categoria</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: Faturas, Contratos, Manuais..."
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Switch
                            id="status"
                            :checked="form.status"
                            @update:checked="form.status = $event"
                        />
                        <Label for="status" class="font-normal cursor-pointer text-muted-foreground">
                            {{ form.status ? 'Ativa' : 'Inativa' }}
                        </Label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="route('document-categories.index')" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                        <PhCheck v-else class="size-4" />
                        <span>Criar Categoria</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
