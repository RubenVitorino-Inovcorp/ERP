<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    PhArrowLeft,
    PhCheck,
    PhArrowClockwise,
    PhImage,
    PhTrash,
    PhUploadSimple
} from '@phosphor-icons/vue';

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

function handlePhotoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.photo = file;
        form.remove_photo = false;
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function removePhoto() {
    form.photo = null;
    form.remove_photo = true;
    photoPreview.value = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
}

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
                    
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:items-start">
                        <!-- Preview Box -->
                        <div class="relative flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted/50">
                            <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" alt="Preview da imagem" />
                            <PhImage v-else class="size-8 text-muted-foreground/50" />
                            
                            <!-- Remove Button (Overlay) -->
                            <button
                                v-if="photoPreview"
                                type="button"
                                @click="removePhoto"
                                class="absolute right-1 top-1 flex size-6 items-center justify-center rounded-full bg-black/60 text-white backdrop-blur-sm transition-colors hover:bg-rose-500"
                                title="Remover imagem"
                            >
                                <PhTrash class="size-3.5" />
                            </button>
                        </div>
                        
                        <!-- Upload Actions -->
                        <div class="flex flex-col justify-center space-y-2 pt-2">
                            <Label for="photo_upload" class="flex cursor-pointer items-center justify-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground">
                                <PhUploadSimple class="size-4" />
                                Escolher Ficheiro
                            </Label>
                            <input
                                id="photo_upload"
                                ref="photoInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                            <p class="text-xs text-muted-foreground text-center sm:text-left">
                                Formatos suportados: JPG, PNG, WEBP.
                                <br />Tamanho máximo: 2MB.
                            </p>
                            <p v-if="form.errors.photo" class="text-xs text-rose-500">{{ form.errors.photo }}</p>
                        </div>
                    </div>
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
