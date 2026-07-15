<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PhArrowLeft,
    PhPencilSimple,
    PhTrash,
    PhImage,
    PhTag,
    PhCoin,
    PhPercent,
} from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    article: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Artigos', href: '/artigos' },
    { title: props.article.name, href: `/artigos/${props.article.id}` },
];

function deleteArticle() {
    router.delete((window as any).route('articles.destroy', props.article.id), {
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

const formattedPrice = new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
}).format(props.article.price);

</script>

<template>
    <Head :title="article.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            
            <div class="space-y-6">
                <!-- Cabeçalho com ações -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <PhTag class="size-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">{{ article.name }}</h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <Badge :variant="article.status ? 'default' : 'secondary'">
                                    {{ article.status ? 'Ativo' : 'Inativo' }}
                                </Badge>
                                <span class="text-xs font-mono text-muted-foreground">{{ article.reference }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button as-child variant="outline" class="gap-1.5">
                            <Link :href="route('articles.index')">
                                <PhArrowLeft class="size-4" />
                                Voltar
                            </Link>
                        </Button>
                        <Button as-child class="gap-1.5">
                            <Link :href="route('articles.edit', article.id)">
                                <PhPencilSimple class="size-4" />
                                Editar
                            </Link>
                        </Button>
                        <DeleteConfirmation @confirm="deleteArticle">
                            <template #trigger>
                                <Button variant="destructive" class="gap-1.5">
                                    <PhTrash class="size-4" />
                                    Eliminar
                                </Button>
                            </template>
                        </DeleteConfirmation>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    
                    <!-- Foto -->
                    <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02] md:col-span-1 flex flex-col items-center justify-center">
                        <h3 class="text-sm font-medium text-muted-foreground self-start mb-2">Fotografia</h3>
                        <div class="relative flex w-full max-w-[200px] aspect-square shrink-0 items-center justify-center overflow-hidden rounded-xl border bg-muted/50">
                            <img v-if="article.photo_path" :src="`/storage/${article.photo_path}`" class="h-full w-full object-cover" :alt="article.name" />
                            <PhImage v-else class="size-12 text-muted-foreground/30" />
                        </div>
                    </div>

                    <div class="space-y-6 md:col-span-2">
                        <!-- Informações Gerais -->
                        <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                            <h3 class="text-lg font-medium text-foreground">Detalhes de Preço</h3>
                            <dl class="space-y-3">
                                <div class="flex items-center gap-3 border-b pb-2">
                                    <PhCoin class="size-4 shrink-0 text-muted-foreground" />
                                    <div class="flex flex-1 items-center justify-between">
                                        <dt class="text-sm text-muted-foreground">Preço Base (Sem IVA)</dt>
                                        <dd class="text-sm font-semibold text-foreground">{{ formattedPrice }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <PhPercent class="size-4 shrink-0 text-muted-foreground" />
                                    <div class="flex flex-1 items-center justify-between">
                                        <dt class="text-sm text-muted-foreground">Taxa de IVA Aplicável</dt>
                                        <dd class="text-sm font-medium text-foreground">
                                            {{ article.vat_rate?.name || '-' }} ({{ article.vat_rate?.value || '0.00' }}%)
                                        </dd>
                                    </div>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Descrição -->
                        <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                            <h3 class="text-lg font-medium text-foreground">Descrição do Artigo</h3>
                            <p v-if="article.description" class="text-sm text-foreground whitespace-pre-wrap">{{ article.description }}</p>
                            <p v-else class="text-sm text-muted-foreground italic">Sem descrição.</p>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02] md:col-span-3">
                        <h3 class="text-lg font-medium text-foreground">Observações Internas</h3>
                        <p v-if="article.notes" class="text-sm text-foreground whitespace-pre-wrap">{{ article.notes }}</p>
                        <p v-else class="text-sm text-muted-foreground italic">Sem observações registadas.</p>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
