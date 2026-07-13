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
    { title: 'Fornecedores', href: '/fornecedores' },
    { title: 'Novo Fornecedor', href: '/fornecedores/criar' },
];
</script>

<template>
    <Head title="Novo Fornecedor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Novo Fornecedor</h1>
                <p class="text-sm text-muted-foreground">Registe um novo fornecedor no sistema.</p>
            </div>
            <EntityForm
                :nextNumber="nextNumber"
                :countries="countries"
                defaultType="fornecedor"
                :submitRoute="route('suppliers.store')"
                :backRoute="route('suppliers.index')"
            />
        </div>
    </AppLayout>
</template>
