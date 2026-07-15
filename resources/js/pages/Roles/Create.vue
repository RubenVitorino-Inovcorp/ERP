<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    permissions: any[];
}>();

const form = useForm({
    name: '',
    status: true,
    permissions: [] as string[],
});

const modulesMap: Record<string, string> = {
    clientes: 'Clientes',
    fornecedores: 'Fornecedores',
    contactos: 'Contactos',
    propostas: 'Propostas',
    calendario: 'Calendário',
    encomendas_clientes: 'Encomendas - Clientes',
    encomendas_fornecedores: 'Encomendas - Fornecedores',
    ordens_trabalho: 'Ordens de Trabalho',
    financeiro: 'Financeiro',
    arquivo_digital: 'Arquivo Digital',
    utilizadores: 'Gestão de Utilizadores',
    permissoes: 'Gestão de Permissões',
    configuracoes: 'Configurações',
};

const actions = ['read', 'create', 'update', 'delete'];
const actionsLabels: Record<string, string> = {
    read: 'Ler',
    create: 'Criar',
    update: 'Editar',
    delete: 'Eliminar',
};

// Computed helper para facilitar a renderização da matriz
const permissionsMatrix = computed(() => {
    const matrix: any = {};
    
    Object.keys(modulesMap).forEach(mod => {
        matrix[mod] = {
            label: modulesMap[mod],
            actions: {}
        };
        
        actions.forEach(act => {
            const permName = `${mod}.${act}`;
            matrix[mod].actions[act] = permName;
        });
    });
    
    return matrix;
});

function togglePermission(permName: string, checked: boolean | string) {
    const isChecked = checked === true || checked === 'indeterminate';
    if (isChecked) {
        if (!form.permissions.includes(permName)) {
            form.permissions = [...form.permissions, permName];
        }
    } else {
        form.permissions = form.permissions.filter(p => p !== permName);
    }
}

// Verifica se todas as permissões de um módulo estão selecionadas
function isModuleAllSelected(mod: string) {
    const modActions = Object.values(permissionsMatrix.value[mod].actions) as string[];

    if (modActions.length === 0) {
        return false;
    }

    return modActions.every(permName => form.permissions.includes(permName));
}

// Selecionar/desselecionar todas as permissões de um módulo
function toggleModuleAll(mod: string, checked: boolean | string) {
    const isChecked = checked === true || checked === 'indeterminate';
    const modActions = Object.values(permissionsMatrix.value[mod].actions) as string[];
    
    if (isChecked) {
        const newPerms = new Set(form.permissions);
        modActions.forEach(p => newPerms.add(p));
        form.permissions = Array.from(newPerms);
    } else {
        form.permissions = form.permissions.filter(p => !modActions.includes(p));
    }
}

// Verifica se TODAS as permissões de TODOS os módulos estão selecionadas
const isAllSelected = computed(() => {
    const allActions: string[] = [];
    Object.values(permissionsMatrix.value).forEach((modData: any) => {
        allActions.push(...Object.values(modData.actions) as string[]);
    });
    
    if (allActions.length === 0) return false;
    return allActions.every(permName => form.permissions.includes(permName));
});

// Selecionar/desselecionar absolutamente TUDO
function toggleAll(checked: boolean | string) {
    const isChecked = checked === true || checked === 'indeterminate';
    if (isChecked) {
        const allActions: string[] = [];
        Object.values(permissionsMatrix.value).forEach((modData: any) => {
            allActions.push(...Object.values(modData.actions) as string[]);
        });
        
        const newPerms = new Set(form.permissions);
        allActions.forEach(p => newPerms.add(p));
        form.permissions = Array.from(newPerms);
    } else {
        form.permissions = [];
    }
}

function submit() {
    form.post(route('roles.store'), {
        onSuccess: () => {
            // O success toast é lidado pelo layout/global baseado no flash, mas se necessário:
            // toast.success('Grupo criado com sucesso');
        },
        onError: () => {
            toast.error('Ocorreu um erro ao guardar o grupo. Verifique os campos.');
        },
    });
}
</script>

<template>
    <Head title="Novo Grupo de Permissões" />
    
    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        Novo Grupo de Permissões
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Preencha os dados abaixo para criar um novo grupo.
                    </p>
                </div>
                <Button variant="outline" as-child class="gap-2">
                    <Link :href="route('roles.index')">
                        <PhArrowLeft class="size-4" />
                        Voltar
                    </Link>
                </Button>
            </div>

            <div class="rounded-xl border bg-card p-6 text-card-foreground shadow-sm">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Configurações Gerais -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-foreground">Detalhes do Grupo</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Nome e estado do grupo de acessos.</p>
                        </div>
                        
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Nome do Grupo</Label>
                                <Input 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="ex: Administradores"
                                    :class="{ 'border-destructive': form.errors.name }"
                                />
                                <span v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</span>
                            </div>
                            
                            <div class="flex items-center space-x-2 pt-8">
                                <Switch 
                                    id="status" 
                                    :checked="form.status" 
                                    @update:checked="form.status = $event" 
                                />
                                <Label for="status" class="font-normal cursor-pointer text-muted-foreground">
                                    {{ form.status ? 'Grupo Ativo' : 'Grupo Inativo' }}
                                </Label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t"></div>
                    
                    <!-- Matriz de Permissões -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-foreground">Matriz de Acessos</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Selecione as ações que os utilizadores deste grupo podem realizar em cada menu.
                            </p>
                        </div>
                        
                        <div class="rounded-md border overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-muted/50 text-xs text-muted-foreground uppercase">
                                    <tr>
                                        <th class="px-6 py-4 font-medium">Módulo / Menu</th>
                                        <th class="px-6 py-4 font-medium text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <span>Selecionar Tudo</span>
                                                <Checkbox 
                                                    :checked="isAllSelected"
                                                    @update:checked="toggleAll"
                                                    class="data-[state=checked]:bg-primary"
                                                />
                                            </div>
                                        </th>
                                        <th v-for="action in actions" :key="action" class="px-6 py-4 font-medium text-center">
                                            {{ actionsLabels[action] }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(moduleData, moduleKey) in permissionsMatrix" :key="moduleKey" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-foreground">
                                            {{ moduleData.label }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center">
                                                <Checkbox 
                                                    :checked="isModuleAllSelected(moduleKey as string)"
                                                    @update:checked="(checked) => toggleModuleAll(moduleKey as string, checked)"
                                                    class="data-[state=checked]:bg-primary"
                                                />
                                            </div>
                                        </td>
                                        <td v-for="action in actions" :key="action" class="px-6 py-4 text-center">
                                            <div class="flex justify-center">
                                                <Checkbox 
                                                    v-if="moduleData.actions[action]"
                                                    :checked="form.permissions.includes(moduleData.actions[action])"
                                                    @update:checked="(checked) => togglePermission(moduleData.actions[action], checked)"
                                                />
                                                <span v-else class="text-muted-foreground/30">-</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end border-t pt-6">
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <PhFloppyDisk class="size-4" />
                            {{ form.processing ? 'A Guardar...' : 'Guardar Grupo' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
