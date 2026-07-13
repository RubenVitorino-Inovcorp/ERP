<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue'

import { ref, watch } from 'vue'

const props = defineProps<{
    contact: {
        id: number
        entity_id: number
        name: string
        last_name: string
        contact_function_id: number
        phone: string | null
        mobile: string | null
        email: string
        gdpr_consent: boolean
        notes: string | null
        status: boolean
    }
    entities: { id: number; name: string; type: string; nif: string }[]
    functions: { id: number; name: string }[]
}>()

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Contactos',
        href: '/contactos',
    },
    {
        title: `Editar Contacto #${props.contact.id}`,
        href: '#',
    },
]

const form = useForm({
    entity_id: String(props.contact.entity_id),
    name: props.contact.name,
    last_name: props.contact.last_name,
    contact_function_id: String(props.contact.contact_function_id),
    phone: props.contact.phone || '',
    mobile: props.contact.mobile || '',
    email: props.contact.email,
    gdpr_consent: props.contact.gdpr_consent,
    notes: props.contact.notes || '',
    status: props.contact.status,
})

const localGdprConsent = ref(form.gdpr_consent)
const localStatus = ref(form.status)

watch(localGdprConsent, (val) => {
    form.gdpr_consent = val
})

watch(localStatus, (val) => {
    form.status = val
})

function submit() {
    form.put(`/contactos/${props.contact.id}`)
}
</script>

<template>
    <Head title="Editar Contacto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Editar Contacto
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Edite os dados associados a este contacto.
                    </p>
                </div>
                <Link href="/contactos">
                    <Button variant="outline" class="gap-2">
                        <PhArrowLeft class="size-4" />
                        Voltar
                    </Button>
                </Link>
            </div>

            <!-- Formulário -->
            <form @submit.prevent="submit" class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    
                    <!-- Entidade -->
                    <FormField name="entity_id">
                        <FormItem>
                            <FormLabel>Entidade Associada <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.entity_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.entity_id }">
                                        <SelectValue placeholder="Selecione uma entidade" />
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
                            </FormControl>
                            <FormMessage v-if="form.errors.entity_id">{{ form.errors.entity_id }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Função -->
                    <FormField name="contact_function_id">
                        <FormItem>
                            <FormLabel>Função <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.contact_function_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.contact_function_id }">
                                        <SelectValue placeholder="Selecione uma função" />
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
                            </FormControl>
                            <FormMessage v-if="form.errors.contact_function_id">{{ form.errors.contact_function_id }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Nome -->
                    <FormField name="name">
                        <FormItem>
                            <FormLabel>Nome Próprio <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.name">{{ form.errors.name }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Apelido -->
                    <FormField name="last_name">
                        <FormItem>
                            <FormLabel>Apelido <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input v-model="form.last_name" :class="{ 'border-destructive': form.errors.last_name }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.last_name">{{ form.errors.last_name }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Email -->
                    <FormField name="email">
                        <FormItem>
                            <FormLabel>Email <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Input type="email" v-model="form.email" :class="{ 'border-destructive': form.errors.email }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.email">{{ form.errors.email }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <div class="hidden md:block"></div> <!-- Spacer -->

                    <!-- Telefone -->
                    <FormField name="phone">
                        <FormItem>
                            <FormLabel>Telefone</FormLabel>
                            <FormControl>
                                <Input v-model="form.phone" @input="form.phone = $event.target.value.replace(/[^0-9+\s]/g, '')" :class="{ 'border-destructive': form.errors.phone }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.phone">{{ form.errors.phone }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Telemóvel -->
                    <FormField name="mobile">
                        <FormItem>
                            <FormLabel>Telemóvel</FormLabel>
                            <FormControl>
                                <Input v-model="form.mobile" @input="form.mobile = $event.target.value.replace(/[^0-9+\s]/g, '')" :class="{ 'border-destructive': form.errors.mobile }" />
                            </FormControl>
                            <FormMessage v-if="form.errors.mobile">{{ form.errors.mobile }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Observações -->
                    <div class="md:col-span-2">
                        <FormField name="notes">
                            <FormItem>
                                <FormLabel>Observações</FormLabel>
                                <FormControl>
                                    <Textarea v-model="form.notes" :class="{ 'border-destructive': form.errors.notes }" rows="4" />
                                </FormControl>
                                <FormMessage v-if="form.errors.notes">{{ form.errors.notes }}</FormMessage>
                            </FormItem>
                        </FormField>
                    </div>

                    <!-- Toggles -->
                    <div class="md:col-span-2 flex flex-col sm:flex-row gap-8 pt-4 border-t">
                        <!-- RGPD -->
                        <div class="flex items-start gap-3 w-full sm:w-1/2 p-2">
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

                        <!-- Status -->
                        <div class="flex items-start gap-3 w-full sm:w-1/2 p-2">
                            <Checkbox
                                id="status"
                                v-model="localStatus"
                                class="mt-0.5"
                            />
                            <label for="status" class="cursor-pointer">
                                <span class="text-sm font-medium leading-none">Estado</span>
                                <p class="text-xs text-muted-foreground mt-0.5">Se inativo, não será apresentado nas listagens públicas.</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer do Form -->
                <div class="mt-8 flex items-center justify-end gap-4 border-t pt-6">
                    <Link href="/contactos">
                        <Button type="button" variant="ghost">Cancelar</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="gap-2">
                        <PhFloppyDisk class="size-4" />
                        Gravar Alterações
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
