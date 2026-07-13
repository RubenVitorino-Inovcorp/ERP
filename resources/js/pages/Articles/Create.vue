<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ArticleForm from '@/components/ArticleForm.vue';

defineProps<{
    vatRates: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Artigos', href: '/artigos' },
    { title: 'Novo Artigo', href: '/artigos/criar' },
];
</script>

<template>
    <Head title="Novo Artigo" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Novo Artigo</h1>
                <p class="text-sm text-muted-foreground">Adicione um novo produto ou serviço ao catálogo.</p>
            </div>
            <ArticleForm
                :vatRates="vatRates"
                :submitRoute="route('articles.store')"
                :backRoute="route('articles.index')"
            />
        </div>
    </AppLayout>
</template>
