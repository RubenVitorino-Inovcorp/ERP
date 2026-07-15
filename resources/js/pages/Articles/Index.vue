<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PhPlus, PhImage } from '@phosphor-icons/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    articles: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: Record<string, any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Artigos', href: '/artigos' },
];

function deleteArticle(id: number) {
    router.delete((window as any).route('articles.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Artigo eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o artigo.');
        },
    });
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'photo',
        header: 'Foto',
        enableSorting: false,
        cell: ({ row }) => {
            const path = row.original.photo_path;

            if (path) {
                return h('div', { class: 'flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-muted' }, [
                    h('img', {
                        src: `/storage/${path}`,
                        class: 'size-full object-cover',
                        alt: row.original.name,
                    }),
                ]);
            }

            return h('div', { class: 'flex size-10 shrink-0 items-center justify-center rounded-md border bg-muted text-muted-foreground' }, [
                h(PhImage, { class: 'size-5 opacity-50' }),
            ]);
        },
    },
    {
        accessorKey: 'reference',
        header: 'Referência',
        enableSorting: true,
        cell: ({ row }) => {
            const article = row.original;
            const wRoute = (window as any).route;

            return h(
                Link,
                {
                    href: wRoute('articles.show', article.id),
                    class: 'font-mono font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => article.reference,
            );
        },
    },
    {
        accessorKey: 'name',
        header: 'Nome',
        enableSorting: true,
        cell: ({ row }) => {
            const article = row.original;
            const wRoute = (window as any).route;

            return h(
                Link,
                {
                    href: wRoute('articles.show', article.id),
                    class: 'font-medium text-primary transition-colors hover:text-foreground hover:font-semibold',
                },
                () => article.name,
            );
        },
    },
    {
        accessorKey: 'description',
        header: 'Descrição',
        enableSorting: false,
        cell: ({ row }) => {
            const desc = row.original.description;

            return desc ? h('span', { class: 'truncate max-w-[200px] inline-block', title: desc }, desc) : '-';
        },
    },
    {
        accessorKey: 'price',
        header: 'Preço Base',
        enableSorting: true,
        cell: ({ row }) => {
            const amount = parseFloat(row.original.price);
            const formatted = new Intl.NumberFormat('pt-PT', {
                style: 'currency',
                currency: 'EUR',
            }).format(amount);
            
            return h('div', { class: 'font-medium' }, formatted);
        },
    },
    {
        id: 'actions',
        header: 'Ações',
        enableSorting: false,
        cell: ({ row }) => {
            const article = row.original;
            const wRoute = (window as any).route;

            return h('div', { class: 'flex items-center gap-1' }, [
                h(
                    Link,
                    {
                        href: wRoute('articles.edit', article.id),
                        class:
                            'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors',
                    },
                    () => 'Editar',
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteArticle(article.id),
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Artigos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho da Página -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Catálogo de Artigos
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de produtos e serviços para faturação e propostas.
                    </p>
                </div>
                <Button as-child class="gap-2">
                    <Link :href="route('articles.create')">
                        <PhPlus class="size-4" weight="bold" />
                        Adicionar Artigo
                    </Link>
                </Button>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :paginated="articles"
                :filters="filters"
                searchPlaceholder="Pesquisar por referência, nome ou descrição..."
            />
        </div>
    </AppLayout>
</template>
