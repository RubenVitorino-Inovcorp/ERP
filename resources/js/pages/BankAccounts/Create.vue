<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as bankAccountsIndex, store as bankAccountsStore } from '@/routes/contas-bancarias';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '#' },
    { title: 'Contas Bancárias', href: bankAccountsIndex().url },
    { title: 'Nova Conta', href: '#' },
];

const form = useForm({
    name: '',
    bank_name: '',
    iban: '',
    swift: '',
    status: true,
});

const submit = () => {
    form.post(bankAccountsStore().url, {
        onSuccess: () => {
            toast.success('Conta bancária criada com sucesso.');
        },
    });
};
</script>

<template>
    <Head title="Nova Conta Bancária" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Detalhes da Conta</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Nome da Conta <span class="text-destructive">*</span></Label>
                                    <Input id="name" v-model="form.name" placeholder="Ex: Conta Principal" required />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="bank_name">Nome do Banco</Label>
                                    <Input id="bank_name" v-model="form.bank_name" placeholder="Ex: Millennium BCP" />
                                    <p v-if="form.errors.bank_name" class="text-sm text-destructive">{{ form.errors.bank_name }}</p>
                                </div>

                                <div class="space-y-2 sm:col-span-2">
                                    <Label for="iban">IBAN <span class="text-destructive">*</span></Label>
                                    <Input id="iban" v-model="form.iban" placeholder="PT50..." required />
                                    <p v-if="form.errors.iban" class="text-sm text-destructive">{{ form.errors.iban }}</p>
                                </div>

                                <div class="space-y-2 sm:col-span-2">
                                    <Label for="swift">SWIFT / BIC</Label>
                                    <Input id="swift" v-model="form.swift" placeholder="Código SWIFT do Banco" />
                                    <p v-if="form.errors.swift" class="text-sm text-destructive">{{ form.errors.swift }}</p>
                                </div>

                                <div class="flex items-center space-x-2 sm:col-span-2">
                                    <Switch id="status" :checked="form.status" @update:checked="form.status = $event" />
                                    <Label for="status">Conta Ativa</Label>
                                </div>
                            </div>

                            <div class="flex justify-end gap-4 border-t pt-4">
                                <Button type="button" variant="outline" as-child>
                                    <Link :href="bankAccountsIndex().url">Cancelar</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    Guardar Conta
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
