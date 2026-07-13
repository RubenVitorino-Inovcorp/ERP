<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import EntityShow from '@/components/EntityShow.vue';

const props = defineProps<{
    entity: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Fornecedores', href: '/fornecedores' },
    { title: props.entity.name, href: `/fornecedores/${props.entity.id}` },
];
</script>

<template>
    <Head :title="entity.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            <EntityShow
                :entity="entity"
                entityLabel="Fornecedor"
                :editRoute="route('suppliers.edit', entity.id)"
                :backRoute="route('suppliers.index')"
                :destroyRoute="route('suppliers.destroy', entity.id)"
            />
        </div>
    </AppLayout>
</template>
