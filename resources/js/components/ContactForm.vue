<script setup lang="ts">
import { ref, computed, watch } from 'vue';
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
} from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    contact?: any;
    nextNumber?: number;
    entities: any[];
    functions: any[];
    submitRoute: string;
    backRoute: string;
}>();

const form = useForm({
    number: props.contact?.number || props.nextNumber || '',
    entity_id: props.contact?.entity_id ? String(props.contact.entity_id) : '',
    first_name: props.contact?.first_name || '',
    last_name: props.contact?.last_name || '',
    contact_function_id: props.contact?.contact_function_id ? String(props.contact.contact_function_id) : '',
    phone: props.contact?.phone || '',
    mobile: props.contact?.mobile || '',
    email: props.contact?.email || '',
    gdpr_consent: props.contact ? Boolean(props.contact.gdpr_consent) : false,
    notes: props.contact?.notes || '',
    status: props.contact ? (props.contact.status === 1 || props.contact.status === true) : true,
});

const phoneValue = computed({
    get: () => form.phone,
    set: (val: string) => {
        const hasPlus = val.startsWith('+');
        const digits = val.replace(/[^\d\s]/g, '');
        form.phone = hasPlus ? '+' + digits : digits;
    }
});

const mobileValue = computed({
    get: () => form.mobile,
    set: (val: string) => {
        const hasPlus = val.startsWith('+');
        const digits = val.replace(/[^\d\s]/g, '');
        form.mobile = hasPlus ? '+' + digits : digits;
    }
});

const localGdprConsent = ref(form.gdpr_consent);
const localStatus = ref(form.status);

watch(localGdprConsent, (val) => {
    form.gdpr_consent = val;
});

watch(localStatus, (val) => {
    form.status = val;
});

function submit() {
    if (props.contact) {
        form.put(props.submitRoute);
    } else {
        form.post(props.submitRoute);
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Informações Gerais -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h2 class="text-lg font-medium text-foreground">Informações Gerais</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Número -->
                    <div class="space-y-2">
                        <Label for="number" class="text-sm font-medium">Número</Label>
                        <Input
                            id="number"
                            type="text"
                            :value="contact?.number || nextNumber"
                            disabled
                            class="bg-muted opacity-80"
                        />
                    </div>

                    <!-- Função -->
                    <div class="space-y-2">
                        <Label for="contact_function_id" class="text-sm font-medium">Função</Label>
                        <Select v-model="form.contact_function_id">
                            <SelectTrigger id="contact_function_id">
                                <SelectValue placeholder="Selecione a função..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="func in functions"
                                    :key="func.id"
                                    :value="String(func.id)"
                                >
                                    {{ func.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.contact_function_id" class="text-xs text-rose-500">{{ form.errors.contact_function_id }}</p>
                    </div>
                </div>

                <!-- Entidade -->
                <div class="space-y-2">
                    <Label for="entity_id" class="text-sm font-medium">Entidade Associada</Label>
                    <Select v-model="form.entity_id">
                        <SelectTrigger id="entity_id">
                            <SelectValue placeholder="Selecione a entidade..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="entity in entities"
                                :key="entity.id"
                                :value="String(entity.id)"
                            >
                                {{ entity.name }} ({{ entity.nif }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.entity_id" class="text-xs text-rose-500">{{ form.errors.entity_id }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Nome -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium">Nome</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Primeiro nome"
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Apelido -->
                    <div class="space-y-2">
                        <Label for="last_name" class="text-sm font-medium">Apelido</Label>
                        <Input
                            id="last_name"
                            v-model="form.last_name"
                            placeholder="Último nome"
                        />
                        <p v-if="form.errors.last_name" class="text-xs text-rose-500">{{ form.errors.last_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Contactos -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h2 class="text-lg font-medium text-foreground">Contactos</h2>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="Ex: contacto@empresa.com"
                    />
                    <p v-if="form.errors.email" class="text-xs text-rose-500">{{ form.errors.email }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Telefone -->
                    <div class="space-y-2">
                        <Label for="phone" class="text-sm font-medium">Telefone</Label>
                        <Input
                            id="phone"
                            v-model="phoneValue"
                            placeholder="Ex: 210000000"
                        />
                        <p v-if="form.errors.phone" class="text-xs text-rose-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Telemóvel -->
                    <div class="space-y-2">
                        <Label for="mobile" class="text-sm font-medium">Telemóvel</Label>
                        <Input
                            id="mobile"
                            v-model="mobileValue"
                            placeholder="Ex: 910000000"
                        />
                        <p v-if="form.errors.mobile" class="text-xs text-rose-500">{{ form.errors.mobile }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Observações e Estado -->
        <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
            <h2 class="text-lg font-medium text-foreground">Observações e Estado</h2>

            <div class="space-y-2">
                <Textarea
                    id="notes"
                    v-model="form.notes"
                    placeholder="Escreva notas ou observações internas..."
                    class="min-h-[120px]"
                />
                <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
            </div>

            <div class="flex flex-col gap-4 pt-2 sm:flex-row sm:gap-8">
                <!-- Consentimento RGPD -->
                <div class="flex items-start gap-3">
                    <Checkbox
                        id="gdpr_consent"
                        v-model="localGdprConsent"
                        class="mt-0.5"
                    />
                    <label for="gdpr_consent" class="cursor-pointer">
                        <span class="text-sm font-medium leading-none">Consentimento RGPD</span>
                        <p class="text-xs text-muted-foreground mt-0.5">Autorização de tratamento de dados pessoais.</p>
                    </label>
                </div>

                <!-- Estado (Ativo/Inativo) -->
                <div class="flex items-start gap-3">
                    <Checkbox
                        id="status"
                        v-model="localStatus"
                        class="mt-0.5"
                    />
                    <label for="status" class="cursor-pointer">
                        <span class="text-sm font-medium leading-none">Contacto Ativo</span>
                        <p class="text-xs text-muted-foreground mt-0.5">Define se o contacto está disponível para uso.</p>
                    </label>
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
                <span>{{ contact ? 'Guardar Alterações' : 'Criar Contacto' }}</span>
            </Button>
        </div>
    </form>
</template>
