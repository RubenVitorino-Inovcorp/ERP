<script setup lang="ts">
import { h, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import SearchSelect from '@/components/SearchSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { PhPlus, PhDownloadSimple, PhArrowClockwise, PhUploadSimple } from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';
import type { ColumnDef } from '@tanstack/vue-table';
import { ref } from 'vue';

const props = defineProps<{
    digitalFiles: {
        data: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    categories: any[];
    filters: Record<string, any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Arquivo Digital', href: '/arquivo-digital' },
];

const dialogOpen = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const categoryOptions = computed(() =>
    props.categories.map(c => ({ label: c.name, value: c.id }))
);

const form = useForm({
    name: '',
    document_category_id: null as number | null,
    file: null as File | null,
});

const selectedCategory = ref<{ label: string; value: number } | null>(null);

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
        // Preencher automaticamente o nome com o nome do ficheiro (sem extensão) se vazio
        if (!form.name) {
            const fileName = target.files[0].name;
            form.name = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
        }
    }
}

function submit() {
    if (selectedCategory.value) {
        form.document_category_id = selectedCategory.value.value;
    }

    form.post(route('digital-files.store'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Documento carregado com sucesso.');
            dialogOpen.value = false;
            form.reset();
            selectedCategory.value = null;
        },
        onError: () => {
            toast.error('Ocorreu um erro ao carregar o documento. Verifique os campos.');
        },
    });
}

function deleteFile(id: number) {
    router.delete(route('digital-files.destroy', id), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props.flash?.error || (page.props as any).error;
            if (flashError) {
                toast.error(flashError);
            } else {
                toast.success('Documento eliminado com sucesso.');
            }
        },
        onError: () => {
            toast.error('Erro ao eliminar o documento.');
        },
    });
}

// Formatar tamanho do ficheiro
function formatSize(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'name',
        header: 'Nome',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'category',
        header: 'Categoria',
        enableSorting: false,
        cell: ({ row }) => {
            const category = row.original.category;
            return h('span', {}, category ? category.name : '-');
        },
    },
    {
        accessorKey: 'size',
        header: 'Tamanho',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, formatSize(row.original.size)),
    },
    {
        accessorKey: 'created_at',
        header: 'Data',
        enableSorting: true,
        cell: ({ row }) => {
            const date = new Date(row.original.created_at);
            return h('span', { class: 'text-muted-foreground' }, date.toLocaleDateString('pt-PT'));
        },
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) => {
            const file = row.original;
            return h('div', { class: 'flex items-center justify-end gap-1' }, [
                h(
                    'a',
                    {
                        href: route('digital-files.download', file.id),
                        class: 'inline-flex items-center justify-center rounded-md border border-input bg-background px-2.5 py-1 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors gap-1',
                    },
                    [
                        h(PhDownloadSimple, { class: 'size-3.5' }),
                        'Download',
                    ]
                ),
                h(DeleteConfirmation, {
                    onConfirm: () => deleteFile(file.id),
                    description: 'Esta ação irá eliminar permanentemente o documento do servidor.',
                }),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Arquivo Digital" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Arquivo Digital
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Gestão de documentos e ficheiros do sistema.
                    </p>
                </div>

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button class="gap-2">
                            <PhPlus class="size-4" weight="bold" />
                            Carregar Documento
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Carregar Documento</DialogTitle>
                            <DialogDescription>
                                Selecione o ficheiro a carregar para o arquivo digital.
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="file">Ficheiro</Label>
                                <Input
                                    id="file"
                                    ref="fileInput"
                                    type="file"
                                    @change="onFileChange"
                                    class="cursor-pointer"
                                />
                                <p v-if="form.errors.file" class="text-xs text-rose-500">{{ form.errors.file }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="name">Nome do Documento</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Nome descritivo do documento"
                                />
                                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label>Categoria</Label>
                                <SearchSelect
                                    v-model="selectedCategory"
                                    :options="categoryOptions"
                                    placeholder="Selecionar categoria..."
                                />
                                <p v-if="form.errors.document_category_id" class="text-xs text-rose-500">{{ form.errors.document_category_id }}</p>
                            </div>

                            <DialogFooter>
                                <Button type="submit" :disabled="form.processing" class="gap-1.5 w-full">
                                    <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                                    <PhUploadSimple v-else class="size-4" />
                                    {{ form.processing ? 'A carregar...' : 'Carregar' }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <DataTable
                :columns="columns"
                :paginated="digitalFiles"
                :filters="filters"
                searchPlaceholder="Pesquisar por nome do documento..."
            />
        </div>
    </AppLayout>
</template>
