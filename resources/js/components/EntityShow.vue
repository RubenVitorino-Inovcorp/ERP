<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import {
    PhArrowLeft,
    PhPencilSimple,
    PhTrash,
    PhGlobe,
    PhEnvelope,
    PhPhone,
    PhDeviceMobile,
    PhMapPin,
    PhIdentificationCard,
    PhShieldCheck,
} from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    entity: any;
    editRoute: string;
    backRoute: string;
    destroyRoute: string;
    entityLabel: string;
}>();

function deleteEntity() {
    router.delete(props.destroyRoute, {
        onSuccess: () => {
            toast.success(`${props.entityLabel} eliminado com sucesso.`);
        },
        onError: () => {
            toast.error(`Erro ao eliminar o ${props.entityLabel.toLowerCase()}.`);
        },
    });
}

const typeLabels: Record<string, string> = {
    cliente: 'Cliente',
    fornecedor: 'Fornecedor',
    ambos: 'Cliente e Fornecedor',
};
</script>

<template>
    <div class="space-y-6">
        <!-- Cabeçalho com ações -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <PhIdentificationCard class="size-6" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-foreground">{{ entity.name }}</h2>
                    <div class="flex items-center gap-2 mt-0.5">
                        <Badge :variant="entity.status ? 'default' : 'secondary'">
                            {{ entity.status ? 'Ativo' : 'Inativo' }}
                        </Badge>
                        <Badge variant="outline">{{ typeLabels[entity.type] || entity.type }}</Badge>
                        <span class="text-xs text-muted-foreground">Nº {{ entity.number }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button as-child variant="outline" class="gap-1.5">
                    <Link :href="backRoute">
                        <PhArrowLeft class="size-4" />
                        Voltar
                    </Link>
                </Button>
                <Button as-child class="gap-1.5">
                    <Link :href="editRoute">
                        <PhPencilSimple class="size-4" />
                        Editar
                    </Link>
                </Button>
                <DeleteConfirmation @confirm="deleteEntity">
                    <template #trigger>
                        <Button variant="destructive" class="gap-1.5">
                            <PhTrash class="size-4" />
                            Eliminar
                        </Button>
                    </template>
                </DeleteConfirmation>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Informações Fiscais -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h3 class="text-lg font-medium text-foreground">Informações Fiscais</h3>
                <dl class="space-y-3">
                    <div class="flex items-center justify-between border-b pb-2">
                        <dt class="text-sm text-muted-foreground">NIF</dt>
                        <dd class="text-sm font-medium text-foreground">{{ entity.nif }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-b pb-2">
                        <dt class="text-sm text-muted-foreground">País</dt>
                        <dd class="text-sm font-medium text-foreground">
                            {{ entity.country?.name || '-' }}
                            <span v-if="entity.country?.code" class="text-muted-foreground">({{ entity.country.code }})</span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="flex items-center gap-1.5 text-sm text-muted-foreground">
                            <PhShieldCheck class="size-4" />
                            Consentimento RGPD
                        </dt>
                        <dd>
                            <Badge :variant="entity.gdpr_consent ? 'default' : 'secondary'">
                                {{ entity.gdpr_consent ? 'Sim' : 'Não' }}
                            </Badge>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Morada -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h3 class="flex items-center gap-2 text-lg font-medium text-foreground">
                    <PhMapPin class="size-5 text-muted-foreground" />
                    Morada
                </h3>
                <dl class="space-y-3">
                    <div class="border-b pb-2">
                        <dt class="text-xs text-muted-foreground mb-0.5">Morada</dt>
                        <dd class="text-sm font-medium text-foreground">{{ entity.address || '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-muted-foreground mb-0.5">Código Postal</dt>
                            <dd class="text-sm font-medium text-foreground">{{ entity.zip_code || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground mb-0.5">Localidade</dt>
                            <dd class="text-sm font-medium text-foreground">{{ entity.city || '-' }}</dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Contactos -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h3 class="text-lg font-medium text-foreground">Contactos</h3>
                <dl class="space-y-3">
                    <div class="flex items-center gap-3 border-b pb-2">
                        <PhPhone class="size-4 shrink-0 text-muted-foreground" />
                        <div class="flex flex-1 items-center justify-between">
                            <dt class="text-sm text-muted-foreground">Telefone</dt>
                            <dd class="text-sm font-medium text-foreground">{{ entity.phone || '-' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b pb-2">
                        <PhDeviceMobile class="size-4 shrink-0 text-muted-foreground" />
                        <div class="flex flex-1 items-center justify-between">
                            <dt class="text-sm text-muted-foreground">Telemóvel</dt>
                            <dd class="text-sm font-medium text-foreground">{{ entity.mobile || '-' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b pb-2">
                        <PhEnvelope class="size-4 shrink-0 text-muted-foreground" />
                        <div class="flex flex-1 items-center justify-between">
                            <dt class="text-sm text-muted-foreground">Email</dt>
                            <dd class="text-sm font-medium text-foreground">
                                <a :href="`mailto:${entity.email}`" class="text-primary hover:underline">{{ entity.email }}</a>
                            </dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <PhGlobe class="size-4 shrink-0 text-muted-foreground" />
                        <div class="flex flex-1 items-center justify-between">
                            <dt class="text-sm text-muted-foreground">Website</dt>
                            <dd class="text-sm font-medium text-foreground">
                                <a v-if="entity.website" :href="entity.website" target="_blank" class="text-primary hover:underline">{{ entity.website }}</a>
                                <span v-else>-</span>
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Observações -->
            <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                <h3 class="text-lg font-medium text-foreground">Observações</h3>
                <p v-if="entity.notes" class="text-sm text-foreground whitespace-pre-wrap">{{ entity.notes }}</p>
                <p v-else class="text-sm text-muted-foreground italic">Sem observações registadas.</p>
            </div>
        </div>
    </div>
</template>
