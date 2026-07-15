<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import {
    PhArrowLeft,
    PhCheck,
    PhArrowClockwise,
    PhImage,
    PhTrash,
    PhUploadSimple
} from '@phosphor-icons/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';
import { useDropzone } from 'vue3-dropzone';
import { computed } from 'vue';

const props = defineProps<{
    article?: any;
    vatRates: any[];
    submitRoute: string;
    backRoute: string;
}>();

const form = useForm({
    reference: props.article?.reference || '',
    name: props.article?.name || '',
    description: props.article?.description || '',
    price: props.article?.price ? Number(props.article.price) : 0.00,
    vat_rate_id: props.article?.vat_rate_id ? String(props.article.vat_rate_id) : '',
    photo: null as File | null,
    remove_photo: false,
    notes: props.article?.notes || '',
    status: props.article ? (props.article.status === 1 || props.article.status === true) : true,
    _method: props.article ? 'put' : 'post',
});

// Photo upload and preview state
const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(
    props.article?.photo_path ? `/storage/${props.article.photo_path}` : null
);

const localStatus = ref(form.status);

watch(localStatus, (val) => {
    form.status = val;
});

const hasNewPhoto = ref(false);

function onDrop(acceptedFiles: File[], rejectReasons: any[]) {
    if (rejectReasons.length > 0) {
        toast.error('Ficheiro rejeitado. Verifique o tipo e tamanho do ficheiro.');
        return;
    }

    if (acceptedFiles.length > 0) {
        const file = acceptedFiles[0];
        form.photo = file;
        form.remove_photo = false;
        hasNewPhoto.value = true;
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
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

function removePhoto() {
    form.photo = null;
    form.remove_photo = true;
    hasNewPhoto.value = false;
    photoPreview.value = null;

    if (photoInput.value) {
        photoInput.value.value = '';
    }
}

const hasPhoto = computed(() => photoPreview.value !== null);

function submit() {
    // For file uploads in Inertia using PUT method, we must send POST request with _method: 'put'
    // This is already handled by our form data structure and post action
    form.post(props.submitRoute, {
        forceFormData: true,
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Informações Gerais -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02] md:col-span-2 lg:col-span-1">
                <h2 class="text-lg font-medium text-foreground">Detalhes do Artigo</h2>

                <div class="space-y-2">
                    <Label for="reference" class="text-sm font-medium">Referência</Label>
                    <Input
                        id="reference"
                        v-model="form.reference"
                        placeholder="REF001"
                        :disabled="!!article"
                    />
                    <p v-if="form.errors.reference" class="text-xs text-rose-500">{{ form.errors.reference }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium">Nome do Artigo</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Introduza o nome do artigo..."
                    />
                    <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="description" class="text-sm font-medium">Descrição</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Breve descrição do produto..."
                        class="min-h-[100px]"
                    />
                    <p v-if="form.errors.description" class="text-xs text-rose-500">{{ form.errors.description }}</p>
                </div>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="price" class="text-sm font-medium">Preço Base (€)</Label>
                        <Input
                            id="price"
                            type="number"
                            step="0.01"
                            min="0"
                            v-model="form.price"
                            placeholder="0.00"
                        />
                        <p v-if="form.errors.price" class="text-xs text-rose-500">{{ form.errors.price }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="vat_rate_id" class="text-sm font-medium">Taxa de IVA</Label>
                        <Select v-model="form.vat_rate_id">
                            <SelectTrigger id="vat_rate_id">
                                <SelectValue placeholder="Selecione o IVA..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="rate in vatRates"
                                    :key="rate.id"
                                    :value="String(rate.id)"
                                >
                                    {{ rate.name }} ({{ rate.value }}%)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.vat_rate_id" class="text-xs text-rose-500">{{ form.errors.vat_rate_id }}</p>
                    </div>
                </div>
            </div>

            <!-- Imagem e Configurações Adicionais -->
            <div class="space-y-6 md:col-span-2 lg:col-span-1">
                <!-- Fotografia -->
                <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                    <h2 class="text-lg font-medium text-foreground">Fotografia do Artigo</h2>
                    
                    <div class="flex flex-col gap-4">
                        <div v-if="hasPhoto" class="relative shrink-0 w-max">
                            <div class="size-32 rounded-lg border border-border overflow-hidden bg-muted flex items-center justify-center">
                                <img
                                    :src="photoPreview!"
                                    alt="Fotografia do artigo"
                                    class="size-full object-cover"
                                />
                            </div>
                            <button
                                v-if="hasPhoto"
                                type="button"
                                @click="removePhoto"
                                class="absolute -top-2 -right-2 rounded-full bg-destructive p-1 text-white shadow-sm hover:bg-destructive/90 transition-colors"
                            >
                                <PhTrash class="size-3.5" />
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
                    <p v-if="form.errors.photo" class="text-xs text-rose-500">{{ form.errors.photo }}</p>
                </div>

                <!-- Estado -->
                <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                    <h2 class="text-lg font-medium text-foreground">Estado e Visibilidade</h2>
                    
                    <div class="flex items-start gap-3">
                        <Checkbox
                            id="status"
                            v-model="localStatus"
                            class="mt-0.5"
                        />
                        <label for="status" class="cursor-pointer">
                            <span class="text-sm font-medium leading-none">Artigo Ativo</span>
                            <p class="text-xs text-muted-foreground mt-0.5">Define se este artigo está disponível para seleção em propostas e encomendas.</p>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Observações (Full Width) -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02] md:col-span-2">
                <h2 class="text-lg font-medium text-foreground">Observações Internas</h2>
                <div class="space-y-2">
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Escreva notas ou observações internas sobre o artigo..."
                        class="min-h-[120px]"
                    />
                    <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
                </div>
            </div>
        </div>

        <!-- Ações do Formulário -->
        <div class="flex items-center justify-end gap-3 border-t pt-6">
            <Button as-child variant="outline">
                <Link :href="backRoute" class="gap-1.5">
                    <PhArrowLeft class="size-4" />
                    <span>Voltar</span>
                </Link>
            </Button>
            <Button type="submit" :disabled="form.processing" class="gap-1.5">
                <PhArrowClockwise v-if="form.processing" class="size-4 animate-spin" />
                <PhCheck v-else class="size-4" />
                <span>{{ article ? 'Guardar Alterações' : 'Criar Artigo' }}</span>
            </Button>
        </div>
    </form>
</template>
