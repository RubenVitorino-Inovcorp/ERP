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
    { title: 'Clientes', href: '/clientes' },
    { title: 'Editar Cliente', href: `/clientes/${props.entity.id}/editar` },
];
</script>

<template>
    <Head title="Editar Cliente" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar Cliente</h1>
                <p class="text-sm text-muted-foreground">Atualize as informações do cliente no sistema.</p>
            </div>
            <EntityForm
                :entity="entity"
                :countries="countries"
                defaultType="cliente"
                :submitRoute="route('clients.update', entity.id)"
                :backRoute="route('clients.index')"
            />
        </div>
    </AppLayout>
</template>
