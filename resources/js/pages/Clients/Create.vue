<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import EntityForm from '@/components/EntityForm.vue';

defineProps<{
    nextNumber: number;
    countries: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clientes', href: '/clientes' },
    { title: 'Novo Cliente', href: '/clientes/criar' },
];
</script>

<template>
    <Head title="Novo Cliente" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Novo Cliente</h1>
                <p class="text-sm text-muted-foreground">Registe um novo cliente no sistema.</p>
            </div>
            <EntityForm
                :nextNumber="nextNumber"
                :countries="countries"
                defaultType="cliente"
                :submitRoute="route('clients.store')"
                :backRoute="route('clients.index')"
            />
        </div>
    </AppLayout>
</template>
