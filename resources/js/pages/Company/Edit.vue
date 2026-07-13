<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useDropzone } from 'vue3-dropzone';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import {
    PhArrowLeft,
    PhCheck,
    PhArrowClockwise,
    PhUploadSimple,
    PhImage,
    PhTrash,
} from '@phosphor-icons/vue';


const props = defineProps<{
    company: {
        id: number | null;
        name: string | null;
        address: string | null;
        zip_code: string | null;
        city: string | null;
        nif: string | null;
        logo_path: string | null;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Configurações', href: '#' },
    { title: 'Empresa', href: '/configuracoes/empresa' },
];

const form = useForm({
    name: props.company.name ?? '',
    address: props.company.address ?? '',
    zip_code: props.company.zip_code ?? '',
    city: props.company.city ?? '',
    nif: props.company.nif ?? '',
    logo: null as File | null,
});

// Preview da imagem (novo ficheiro selecionado ou logo existente)
const logoPreview = ref<string | null>(null);
const hasNewLogo = ref(false);

// Se já existe um logo guardado, mostrar como preview inicial
if (props.company.logo_path) {
    logoPreview.value = `/storage/${props.company.logo_path}`;
}

// Configuração do vue3-dropzone
function onDrop(acceptedFiles: File[], rejectReasons: any[]) {
    if (rejectReasons.length > 0) {
        toast.error('Ficheiro rejeitado. Verifique o tipo e tamanho do ficheiro.');
        return;
    }

    if (acceptedFiles.length > 0) {
        const file = acceptedFiles[0];
        form.logo = file;
        hasNewLogo.value = true;

        // Gerar preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'],
    maxSize: 10 * 1024 * 1024, // 10MB
    multiple: false,
    maxFiles: 1,
});

function removeLogo() {
    form.logo = null;
    hasNewLogo.value = false;
    logoPreview.value = props.company.logo_path ? `/storage/${props.company.logo_path}` : null;
}

const hasLogo = computed(() => logoPreview.value !== null);

// Formatação automática do código postal (XXXX-XXX)
const zipCodeValue = computed({
    get: () => form.zip_code,
    set: (val: string) => {
        val = val.replace(/[^\d-]/g, '').replace(/-+/g, '-');
        const digits = val.replace(/\D/g, '');
        if (digits.length > 4) {
            val = digits.slice(0, 4) + '-' + digits.slice(4, 7);
        } else {
            val = digits;
        }
        form.zip_code = val.slice(0, 8);
    }
});

// Bloquear caracteres não numéricos no NIF
const nifValue = computed({
    get: () => form.nif,
    set: (val: string) => {
        form.nif = val.replace(/\D/g, '').slice(0, 9);
    }
});

function submit() {
    form.post(route('company.update'), {
        forceFormData: true,
        onSuccess: () => {
            toast.success('Definições da empresa atualizadas com sucesso.');
            hasNewLogo.value = false;
        },
        onError: () => {
            toast.error('Ocorreu um erro ao guardar. Verifique os campos.');
        },
    });
}
</script>

<template>
    <Head title="Configurações da Empresa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-2xl mx-auto w-full">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">Configurações da Empresa</h1>
                <p class="text-sm text-muted-foreground">Personalize os dados da empresa que surgem na aplicação e nos documentos gerados.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <!-- Logotipo -->
                <div class="space-y-2">
                    <Label class="text-sm font-medium">Logotipo da Empresa</Label>

                    <div class="flex items-start gap-6">
                        <!-- Preview do logo -->
                        <div v-if="hasLogo" class="relative shrink-0">
                            <div class="size-24 rounded-lg border border-border overflow-hidden bg-muted flex items-center justify-center">
                                <img
                                    :src="logoPreview!"
                                    alt="Logotipo da empresa"
                                    class="size-full object-contain p-1"
                                />
                            </div>
                            <button
                                v-if="hasNewLogo"
                                type="button"
                                @click="removeLogo"
                                class="absolute -top-2 -right-2 rounded-full bg-destructive p-1 text-white shadow-sm hover:bg-destructive/90 transition-colors"
                            >
                                <PhTrash class="size-3" />
                            </button>
                        </div>

                        <!-- Dropzone -->
                        <div
                            v-bind="getRootProps()"
                            class="flex-1 cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                            :class="{
                                'border-primary bg-primary/5': isDragActive,
                                'border-border hover:border-primary/50 hover:bg-muted/50': !isDragActive,
                            }"
                        >
                            <input v-bind="getInputProps()" />
                            <div class="flex flex-col items-center gap-2">
                                <div class="rounded-full bg-muted p-3">
                                    <PhUploadSimple v-if="!isDragActive" class="size-6 text-muted-foreground" />
                                    <PhImage v-else class="size-6 text-primary" />
                                </div>
                                <div>
                                    <p v-if="isDragActive" class="text-sm font-medium text-primary">
                                        Largue a imagem aqui...
                                    </p>
                                    <p v-else class="text-sm font-medium text-foreground">
                                        Arraste e largue ou <span class="text-primary">clique para selecionar</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        JPG, PNG, GIF, SVG ou WebP (máx. 10MB)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.logo" class="text-xs text-rose-500">{{ form.errors.logo }}</p>
                </div>

                <!-- Nome e NIF -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome da Empresa</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: InovCorp, Lda."
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="nif" class="text-sm font-medium">Número de Contribuinte (NIF)</Label>
                        <Input
                            id="nif"
                            v-model="nifValue"
                            placeholder="Ex: 123456789"
                            maxlength="9"
                            required
                        />
                        <p v-if="form.errors.nif" class="text-xs text-rose-500">{{ form.errors.nif }}</p>
                    </div>
                </div>

                <!-- Morada -->
                <div class="space-y-2">
                    <Label for="address" class="text-sm font-medium">Morada</Label>
                    <Input
                        id="address"
                        v-model="form.address"
                        placeholder="Ex: Rua da Inovação, 123"
                        required
                    />
                    <p v-if="form.errors.address" class="text-xs text-rose-500">{{ form.errors.address }}</p>
                </div>

                <!-- Código Postal e Localidade -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="zip_code" class="text-sm font-medium">Código Postal</Label>
                        <Input
                            id="zip_code"
                            v-model="zipCodeValue"
                            placeholder="XXXX-XXX"
                            maxlength="8"
                            required
                        />
                        <p v-if="form.errors.zip_code" class="text-xs text-rose-500">{{ form.errors.zip_code }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="city" class="text-sm font-medium">Localidade</Label>
                        <Input
                            id="city"
                            v-model="form.city"
                            placeholder="Ex: Lisboa"
                            required
                        />
                        <p v-if="form.errors.city" class="text-xs text-rose-500">{{ form.errors.city }}</p>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end gap-3 border-t pt-6">
                    <Button as-child variant="outline">
                        <Link href="/dashboard" class="gap-1.5">
                            <PhArrowLeft class="size-4" />
                            <span>Voltar</span>
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="gap-1.5">
                        <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                        <PhCheck v-else class="size-4" />
                        <span>Guardar Alterações</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
