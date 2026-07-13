<script setup lang="ts">
import { ref, watch, computed } from 'vue';
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
    PhMagnifyingGlass,
} from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';
import axios from 'axios';

const props = defineProps<{
    entity?: any;
    nextNumber?: number;
    countries: any[];
    defaultType: 'cliente' | 'fornecedor';
    submitRoute: string;
    backRoute: string;
}>();

const isSearchingVies = ref(false);

const defaultCountry = props.countries.find(c => c.code === 'PT');

const form = useForm({
    type: props.entity?.type || props.defaultType || 'cliente',
    nif: props.entity?.nif || '',
    name: props.entity?.name || '',
    address: props.entity?.address || '',
    zip_code: props.entity?.zip_code || '',
    city: props.entity?.city || '',
    country_id: props.entity?.country_id ? String(props.entity.country_id) : (defaultCountry ? String(defaultCountry.id) : ''),
    phone: props.entity?.phone || '',
    mobile: props.entity?.mobile || '',
    website: props.entity?.website || '',
    email: props.entity?.email || '',
    gdpr_consent: props.entity ? Boolean(props.entity.gdpr_consent) : false,
    notes: props.entity?.notes || '',
    status: props.entity ? (props.entity.status === 1 || props.entity.status === true) : true,
});

const localGdprConsent = ref(form.gdpr_consent);
const localStatus = ref(form.status);

watch(localGdprConsent, (val) => {
    form.gdpr_consent = val;
});

watch(localStatus, (val) => {
    form.status = val;
});

async function lookupVies() {
    if (!form.nif) {
        toast.warning('Por favor, introduza um NIF para consulta.');
        return;
    }
    if (!form.country_id) {
        toast.warning('Por favor, selecione um país para a consulta.');
        return;
    }

    isSearchingVies.value = true;
    try {
        const response = await axios.get('/api/vies/lookup', {
            params: {
                nif: form.nif,
                country_id: form.country_id,
            },
        });

        if (response.data.success) {
            form.name = response.data.name || form.name;
            form.address = response.data.address || form.address;
            if (response.data.zip_code) {
                form.zip_code = response.data.zip_code;
            }
            if (response.data.city) {
                form.city = response.data.city;
            }
            toast.success('Dados obtidos e preenchidos com sucesso via VIES.');
        } else {
            toast.error(response.data.message || 'NIF não encontrado no sistema VIES.');
        }
    } catch (error: any) {
        const msg = error.response?.data?.message || 'Ocorreu um erro ao comunicar com o VIES.';
        toast.error(msg);
    } finally {
        isSearchingVies.value = false;
    }
}

function submit() {
    if (props.entity) {
        form.put(props.submitRoute, {
            onSuccess: () => {
                toast.success('Entidade atualizada com sucesso.');
            },
            onError: () => {
                toast.error('Erro ao atualizar entidade. Por favor, verifique os campos.');
            },
        });
    } else {
        form.post(props.submitRoute, {
            onSuccess: () => {
                toast.success('Entidade criada com sucesso.');
            },
            onError: () => {
                toast.error('Erro ao criar entidade. Por favor, verifique os campos.');
            },
        });
    }
}

const nifValue = computed({
    get: () => form.nif,
    set: (val: string) => {
        const selectedCountry = props.countries.find(c => String(c.id) === String(form.country_id));
        if (selectedCountry?.code === 'PT') {
            val = val.replace(/\D/g, '').slice(0, 9);
        }
        form.nif = val;
    }
});

const zipCodeValue = computed({
    get: () => form.zip_code,
    set: (val: string) => {
        const selectedCountry = props.countries.find(c => String(c.id) === String(form.country_id));
        if (selectedCountry?.code === 'PT') {
            val = val.replace(/[^\d-]/g, '').replace(/-+/g, '-');
            const digits = val.replace(/\D/g, '');
            if (digits.length > 4) {
                val = digits.slice(0, 4) + '-' + digits.slice(4, 7);
            } else {
                val = digits;
            }
            val = val.slice(0, 8);
        }
        form.zip_code = val;
    }
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
                            :value="entity?.number || nextNumber"
                            disabled
                            class="bg-muted opacity-80"
                        />
                    </div>

                    <!-- Tipo -->
                    <div class="space-y-2">
                        <Label for="type" class="text-sm font-medium">Tipo</Label>
                        <Select v-model="form.type">
                            <SelectTrigger id="type">
                                <SelectValue placeholder="Selecione o tipo..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cliente">Cliente</SelectItem>
                                <SelectItem value="fornecedor">Fornecedor</SelectItem>
                                <SelectItem value="ambos">Ambos</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.type" class="text-xs text-rose-500">{{ form.errors.type }}</p>
                    </div>
                </div>

                <!-- País -->
                <div class="space-y-2">
                    <Label for="country_id" class="text-sm font-medium">País</Label>
                    <Select v-model="form.country_id">
                        <SelectTrigger id="country_id">
                            <SelectValue placeholder="Selecione o país..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="country in countries"
                                :key="country.id"
                                :value="String(country.id)"
                            >
                                {{ country.name }} ({{ country.code }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.country_id" class="text-xs text-rose-500">{{ form.errors.country_id }}</p>
                </div>

                <!-- NIF -->
                <div class="space-y-2">
                    <Label for="nif" class="text-sm font-medium">NIF</Label>
                    <div class="flex gap-2">
                        <Input
                            id="nif"
                            v-model="nifValue"
                            placeholder="Ex: 500000000"
                            class="flex-1"
                        />
                        <Button
                            type="button"
                            variant="secondary"
                            :disabled="isSearchingVies"
                            @click="lookupVies"
                            class="shrink-0 gap-1.5"
                        >
                            <PhArrowClockwise v-if="isSearchingVies" class="size-4 animate-spin" />
                            <PhMagnifyingGlass v-else class="size-4" />
                            <span>VIES</span>
                        </Button>
                    </div>
                    <p v-if="form.errors.nif" class="text-xs text-rose-500">{{ form.errors.nif }}</p>
                </div>

                <!-- Nome -->
                <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium">Nome</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Nome da Entidade"
                    />
                    <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                </div>
            </div>

            <!-- Morada e Contactos -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h2 class="text-lg font-medium text-foreground">Localização e Contactos</h2>

                <!-- Morada -->
                <div class="space-y-2">
                    <Label for="address" class="text-sm font-medium">Morada</Label>
                    <Input
                        id="address"
                        v-model="form.address"
                        placeholder="Rua, número, andar..."
                    />
                    <p v-if="form.errors.address" class="text-xs text-rose-500">{{ form.errors.address }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Código Postal -->
                    <div class="space-y-2">
                        <Label for="zip_code" class="text-sm font-medium">Código Postal</Label>
                        <Input
                            id="zip_code"
                            v-model="zipCodeValue"
                            placeholder="XXXX-XXX"
                        />
                        <p v-if="form.errors.zip_code" class="text-xs text-rose-500">{{ form.errors.zip_code }}</p>
                    </div>

                    <!-- Localidade -->
                    <div class="space-y-2">
                        <Label for="city" class="text-sm font-medium">Localidade</Label>
                        <Input
                            id="city"
                            v-model="form.city"
                            placeholder="Cidade/Vila"
                        />
                        <p v-if="form.errors.city" class="text-xs text-rose-500">{{ form.errors.city }}</p>
                    </div>
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

                <!-- Website -->
                <div class="space-y-2">
                    <Label for="website" class="text-sm font-medium">Website</Label>
                    <Input
                        id="website"
                        v-model="form.website"
                        placeholder="Ex: https://empresa.com"
                    />
                    <p v-if="form.errors.website" class="text-xs text-rose-500">{{ form.errors.website }}</p>
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
                        <span class="text-sm font-medium leading-none">Entidade Ativa</span>
                        <p class="text-xs text-muted-foreground mt-0.5">Define se a entidade pode ser utilizada no sistema.</p>
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
                <span>{{ entity ? 'Guardar Alterações' : 'Criar Entidade' }}</span>
            </Button>
        </div>
    </form>
</template>
