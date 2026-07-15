<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { PhSignIn, PhUserPlus, PhArrowRight } from '@phosphor-icons/vue'
import { Button } from '@/components/ui/button'
import { dashboard, login } from '@/routes'
import { register } from '@/routes'
import AppLogoIcon from '@/components/AppLogoIcon.vue'

defineProps<{
    canLogin: boolean
    canRegister: boolean
}>()
</script>

<template>
    <Head title="Bem-vindo" />

    <div class="flex min-h-svh flex-col items-center justify-center bg-muted/30 p-6">
        <div class="w-full max-w-md space-y-8 text-center">
            
            <!-- Logo -->
            <div class="flex justify-center">
                <img
                    v-if="$page.props.company?.logo_path"
                    :src="`/storage/${$page.props.company.logo_path}`"
                    :alt="$page.props.company?.name ?? 'ERP'"
                    class="h-16 w-auto object-contain"
                />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                    <AppLogoIcon class="size-8 fill-current" />
                </div>
            </div>

            <!-- Título e Subtítulo -->
            <div class="space-y-3">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Sistema de Gestão Empresarial
                </h1>
                <p class="text-muted-foreground">
                    Plataforma integrada para gestão de clientes, fornecedores, propostas, encomendas, 
                    faturação e ordens de trabalho. Tudo num único lugar.
                </p>
            </div>

            <!-- Ações -->
            <div class="flex flex-col items-center justify-center gap-3 pt-4 sm:flex-row">
                <template v-if="$page.props.auth.user">
                    <Link :href="dashboard()" class="w-full sm:w-auto">
                        <Button size="lg" class="w-full gap-2">
                            Ir para o Dashboard
                            <PhArrowRight class="size-4" />
                        </Button>
                    </Link>
                </template>
                <template v-else>
                    <Link v-if="canLogin" :href="login()" class="w-full sm:w-auto">
                        <Button size="lg" class="w-full gap-2">
                            <PhSignIn class="size-4" />
                            Iniciar Sessão
                        </Button>
                    </Link>
                    <Link v-if="canRegister" :href="register()" class="w-full sm:w-auto">
                        <Button size="lg" variant="outline" class="w-full gap-2 bg-background">
                            <PhUserPlus class="size-4" />
                            Registar
                        </Button>
                    </Link>
                </template>
            </div>
            
            <div v-if="$page.props.company?.name" class="pt-8 text-sm text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} {{ $page.props.company.name }}
            </div>
        </div>
    </div>
</template>
