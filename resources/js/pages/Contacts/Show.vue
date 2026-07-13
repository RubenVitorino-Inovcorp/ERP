<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DeleteConfirmation from '@/components/DeleteConfirmation.vue';
import {
    PhArrowLeft,
    PhPencilSimple,
    PhTrash,
    PhEnvelope,
    PhPhone,
    PhDeviceMobile,
    PhIdentificationCard,
    PhShieldCheck,
    PhBuildings,
    PhBriefcase,
} from '@phosphor-icons/vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    contact: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Contactos', href: '/contactos' },
    { title: `${props.contact.name} ${props.contact.last_name}`, href: `/contactos/${props.contact.id}` },
];

function deleteContact() {
    router.delete((window as any).route('contacts.destroy', props.contact.id), {
        onSuccess: () => {
            toast.success('Contacto eliminado com sucesso.');
        },
        onError: () => {
            toast.error('Erro ao eliminar o contacto.');
        },
    });
}
</script>

<template>
    <Head :title="`${contact.name} ${contact.last_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 max-w-5xl mx-auto w-full">
            
            <div class="space-y-6">
                <!-- Cabeçalho com ações -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <PhIdentificationCard class="size-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">{{ contact.name }} {{ contact.last_name }}</h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <Badge :variant="contact.status ? 'default' : 'secondary'">
                                    {{ contact.status ? 'Ativo' : 'Inativo' }}
                                </Badge>
                                <span class="text-xs text-muted-foreground">Nº {{ contact.number }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button as-child variant="outline" class="gap-1.5">
                            <Link :href="route('contacts.index')">
                                <PhArrowLeft class="size-4" />
                                Voltar
                            </Link>
                        </Button>
                        <Button as-child class="gap-1.5">
                            <Link :href="route('contacts.edit', contact.id)">
                                <PhPencilSimple class="size-4" />
                                Editar
                            </Link>
                        </Button>
                        <DeleteConfirmation @confirm="deleteContact">
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
                    <!-- Informações Gerais -->
                    <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                        <h3 class="text-lg font-medium text-foreground">Informações Gerais</h3>
                        <dl class="space-y-3">
                            <div class="flex items-center justify-between border-b pb-2">
                                <dt class="flex items-center gap-1.5 text-sm text-muted-foreground">
                                    <PhBriefcase class="size-4" />
                                    Função
                                </dt>
                                <dd class="text-sm font-medium text-foreground">
                                    {{ contact.contact_function?.name || '-' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between border-b pb-2">
                                <dt class="flex items-center gap-1.5 text-sm text-muted-foreground">
                                    <PhBuildings class="size-4" />
                                    Entidade Associada
                                </dt>
                                <dd class="text-sm font-medium text-primary hover:underline">
                                    <Link v-if="contact.entity" :href="route(contact.entity.type === 'fornecedor' ? 'suppliers.show' : 'clients.show', contact.entity.id)">
                                        {{ contact.entity.name }}
                                    </Link>
                                    <span v-else class="text-foreground no-underline">-</span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="flex items-center gap-1.5 text-sm text-muted-foreground">
                                    <PhShieldCheck class="size-4" />
                                    Consentimento RGPD
                                </dt>
                                <dd>
                                    <Badge :variant="contact.gdpr_consent ? 'default' : 'secondary'">
                                        {{ contact.gdpr_consent ? 'Sim' : 'Não' }}
                                    </Badge>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Contactos -->
                    <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02]">
                        <h3 class="text-lg font-medium text-foreground">Detalhes de Contacto</h3>
                        <dl class="space-y-3">
                            <div class="flex items-center gap-3 border-b pb-2">
                                <PhEnvelope class="size-4 shrink-0 text-muted-foreground" />
                                <div class="flex flex-1 items-center justify-between">
                                    <dt class="text-sm text-muted-foreground">Email</dt>
                                    <dd class="text-sm font-medium text-foreground">
                                        <a :href="`mailto:${contact.email}`" class="text-primary hover:underline">{{ contact.email }}</a>
                                    </dd>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 border-b pb-2">
                                <PhPhone class="size-4 shrink-0 text-muted-foreground" />
                                <div class="flex flex-1 items-center justify-between">
                                    <dt class="text-sm text-muted-foreground">Telefone</dt>
                                    <dd class="text-sm font-medium text-foreground">{{ contact.phone || '-' }}</dd>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <PhDeviceMobile class="size-4 shrink-0 text-muted-foreground" />
                                <div class="flex flex-1 items-center justify-between">
                                    <dt class="text-sm text-muted-foreground">Telemóvel</dt>
                                    <dd class="text-sm font-medium text-foreground">{{ contact.mobile || '-' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <!-- Observações -->
                    <div class="space-y-4 rounded-xl border bg-white p-6 shadow-sm dark:bg-white/[0.02] md:col-span-2">
                        <h3 class="text-lg font-medium text-foreground">Observações</h3>
                        <p v-if="contact.notes" class="text-sm text-foreground whitespace-pre-wrap">{{ contact.notes }}</p>
                        <p v-else class="text-sm text-muted-foreground italic">Sem observações registadas.</p>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
