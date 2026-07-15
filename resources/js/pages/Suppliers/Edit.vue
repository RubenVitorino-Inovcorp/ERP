<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EntityForm from '@/components/EntityForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    entity: any;
    countries: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Fornecedores', href: '/fornecedores' },
    { title: 'Editar Fornecedor', href: `/fornecedores/${props.entity.id}/editar` },
];
</script>

<template>
    <Head title="Editar Fornecedor" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar Fornecedor</h1>
                <p class="text-sm text-muted-foreground">Atualize as informações do fornecedor no sistema.</p>
            </div>
            <EntityForm
                :entity="entity"
                :countries="countries"
                defaultType="fornecedor"
                :submitRoute="route('suppliers.update', entity.id)"
                :backRoute="route('suppliers.index')"
            />
        </div>
    </AppLayout>
</template>
