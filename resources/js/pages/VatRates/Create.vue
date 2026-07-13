<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PhArrowLeft, PhCheck, PhArrowClockwise } from '@phosphor-icons/vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Taxas de IVA', href: '/configuracoes/financeiro/iva' },
    { title: 'Nova Taxa', href: '/configuracoes/financeiro/iva/criar' },
];

const form = useForm({
    name: '',
    value: '',
});

function submit() {
    form.post(route('vat-rates.store'));
}
</script>

<template>
    <Head title="Nova Taxa de IVA" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Nova Taxa de IVA</h1>
                <p class="text-sm text-muted-foreground">Adicione uma nova taxa de IVA para faturação e catálogo.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome da Taxa</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: IVA Normal, IVA Isento, etc."
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="value" class="text-sm font-medium">Valor (%)</Label>
                        <Input
                            id="value"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            v-model="form.value"
                            placeholder="Ex: 23.00, 6.00"
                            required
                        />
                        <p v-if="form.errors.value" class="text-xs text-rose-500">{{ form.errors.value }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link :href="route('vat-rates.index')" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                        <PhCheck v-else class="size-4" />
                        <span>Criar Taxa</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
