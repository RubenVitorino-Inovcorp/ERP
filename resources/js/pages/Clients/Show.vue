<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EntityShow from '@/components/EntityShow.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    entity: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clientes', href: '/clientes' },
    { title: props.entity.name, href: `/clientes/${props.entity.id}` },
];
</script>

<template>
    <Head :title="entity.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            <EntityShow
                :entity="entity"
                entityLabel="Cliente"
                :editRoute="route('clients.edit', entity.id)"
                :backRoute="route('clients.index')"
                :destroyRoute="route('clients.destroy', entity.id)"
            />
        </div>
    </AppLayout>
</template>
