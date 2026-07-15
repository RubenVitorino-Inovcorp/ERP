<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import SearchSelect from '@/components/SearchSelect.vue';
import type { SelectOption } from '@/components/SearchSelect.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    user: any;
    roles: any[];
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Gestão de Acessos', href: '#' },
    { title: 'Utilizadores', href: '/gestao-acessos/utilizadores' },
    { title: 'Editar Utilizador', href: '#' },
];

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
    status: props.user.status === 1 || props.user.status === true,
    roles: (props.user.roles || []).map((r: any) => r.id) as number[],
});

const roleOptions = computed<SelectOption[]>(() => 
    props.roles.map(r => ({ label: r.name, value: r.id }))
);

const selectedRoles = computed({
    get: () => roleOptions.value.filter(o => form.roles.includes(o.value as number)),
    set: (val: any[]) => {
 form.roles = val.map((v: any) => v.value) 
},
});

function submit() {
    form.put(route('users.update', props.user.id), {
        onSuccess: () => {
            toast.success('Utilizador atualizado com sucesso.');
        },
        onError: () => {
            toast.error('Verifique os erros no formulário.');
        }
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Utilizador" />
        
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6 md:pt-0 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight">Editar Utilizador</h1>
                    <p class="text-sm text-muted-foreground">
                        Atualize as informações e os grupos de permissões deste utilizador.
                    </p>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="route('users.index')">
                        <PhArrowLeft class="w-4 h-4 mr-2" />
                        Voltar
                    </Link>
                </Button>
            </div>
            
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nome -->
                        <div class="space-y-2">
                            <Label for="name">Nome <span class="text-destructive">*</span></Label>
                            <Input id="name" v-model="form.name" required placeholder="Ex: João Silva" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        
                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email">Email <span class="text-destructive">*</span></Label>
                            <Input id="email" type="email" v-model="form.email" required placeholder="Ex: joao.silva@inovcorp.pt" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <!-- Telemóvel -->
                        <div class="space-y-2">
                            <Label for="phone">Telemóvel</Label>
                            <Input id="phone" type="tel" v-model="form.phone" placeholder="Ex: 912345678" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Grupo de Permissões -->
                        <div class="space-y-2">
                            <Label>Grupos de Permissões</Label>
                            <SearchSelect 
                                v-model="selectedRoles" 
                                :options="roleOptions" 
                                multiple 
                                placeholder="Selecione um ou mais grupos..." 
                            />
                            <p v-if="form.errors.roles" class="text-sm text-destructive">{{ form.errors.roles }}</p>
                            <p v-if="form.errors['roles.0']" class="text-sm text-destructive">{{ form.errors['roles.0'] }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="status" :checked="form.status" @update:checked="form.status = $event" />
                            <Label for="status" class="cursor-pointer font-normal">
                                Conta Ativa (O utilizador pode fazer login)
                            </Label>
                        </div>
                        <p v-if="form.errors.status" class="text-sm text-destructive mt-1">{{ form.errors.status }}</p>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-6">
                        <Button type="button" variant="outline" as-child>
                            <Link :href="route('users.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <PhFloppyDisk class="w-4 h-4 mr-2" />
                            Guardar Alterações
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
