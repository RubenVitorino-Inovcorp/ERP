<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ArticleForm from '@/components/ArticleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    article: any;
    vatRates: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Artigos', href: '/artigos' },
    { title: 'Editar Artigo', href: `/artigos/${props.article.id}/editar` },
];
</script>

<template>
    <Head title="Editar Artigo" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Editar Artigo</h1>
                <p class="text-sm text-muted-foreground">Atualize as informações do produto ou serviço ({{ article.reference }}).</p>
            </div>
            <ArticleForm
                :article="article"
                :vatRates="vatRates"
                :submitRoute="route('articles.update', article.id)"
                :backRoute="route('articles.index')"
            />
        </div>
    </AppLayout>
</template>
