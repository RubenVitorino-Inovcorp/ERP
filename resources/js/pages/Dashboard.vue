<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    PhUsers,
    PhTruck,
    PhFileText,
    PhShoppingCart,
    PhReceipt,
    PhWrench,
    PhArrowRight,
    PhTrendUp,
    PhWarning,
} from '@phosphor-icons/vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
    stats: {
        clients: number
        suppliers: number
        proposals: { draft: number; closed: number }
        orders: { draft: number; closed: number }
        invoices: { pending: number; paid: number; pending_total: number }
        workOrders: { pending: number; in_progress: number; completed: number }
    }
    recentProposals: {
        id: number
        number: number
        entity_name: string
        status: string
        date: string
        total: number
    }[]
    recentOrders: {
        id: number
        number: number
        entity_name: string
        status: string
        date: string
        total: number
    }[]
}>()

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
]

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value)
}

function formatDate(date: string): string {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('pt-PT')
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho -->
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    Dashboard
                </h1>
                <p class="text-sm text-muted-foreground">
                    Visão geral do sistema ERP.
                </p>
            </div>

            <!-- Cartões de Resumo -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Clientes -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Clientes</CardTitle>
                        <PhUsers class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.clients }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Entidades registadas como clientes
                        </p>
                    </CardContent>
                </Card>

                <!-- Fornecedores -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Fornecedores</CardTitle>
                        <PhTruck class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.suppliers }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Entidades registadas como fornecedores
                        </p>
                    </CardContent>
                </Card>

                <!-- Propostas em Rascunho -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Propostas</CardTitle>
                        <PhFileText class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.proposals.draft }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Em rascunho · {{ stats.proposals.closed }} fechadas
                        </p>
                    </CardContent>
                </Card>

                <!-- Encomendas em Rascunho -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Encomendas</CardTitle>
                        <PhShoppingCart class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.orders.draft }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Em rascunho · {{ stats.orders.closed }} fechadas
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Segunda Linha: Faturas e Ordens -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Faturas Pendentes -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Faturas Pendentes</CardTitle>
                        <PhReceipt class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.invoices.pending }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            <span v-if="stats.invoices.pending > 0" class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                <PhWarning class="size-3" weight="fill" />
                                {{ formatCurrency(Number(stats.invoices.pending_total)) }} por pagar
                            </span>
                            <span v-else>Tudo em dia</span>
                        </p>
                    </CardContent>
                </Card>

                <!-- Faturas Pagas -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Faturas Pagas</CardTitle>
                        <PhTrendUp class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.invoices.paid }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Faturas de fornecedores liquidadas
                        </p>
                    </CardContent>
                </Card>

                <!-- Ordens de Trabalho -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Ordens de Trabalho</CardTitle>
                        <PhWrench class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.workOrders.pending + stats.workOrders.in_progress }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ stats.workOrders.pending }} pendentes · {{ stats.workOrders.in_progress }} em curso · {{ stats.workOrders.completed }} concluídas
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabelas de Atividade Recente -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Últimas Propostas -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Últimas Propostas</CardTitle>
                            <CardDescription>As 5 propostas mais recentes.</CardDescription>
                        </div>
                        <Link href="/propostas" class="text-sm text-primary hover:underline flex items-center gap-1">
                            Ver todas
                            <PhArrowRight class="size-3" />
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentProposals.length === 0" class="text-sm text-muted-foreground text-center py-8">
                            Sem propostas registadas.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="proposal in recentProposals"
                                :key="proposal.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm">#{{ proposal.number }}</span>
                                        <Badge :variant="proposal.status === 'fechado' ? 'default' : 'secondary'">
                                            {{ proposal.status === 'fechado' ? 'Fechado' : 'Rascunho' }}
                                        </Badge>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ proposal.entity_name }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-sm">{{ formatCurrency(proposal.total) }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(proposal.date) }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Últimas Encomendas -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Últimas Encomendas</CardTitle>
                            <CardDescription>As 5 encomendas de clientes mais recentes.</CardDescription>
                        </div>
                        <Link href="/encomendas" class="text-sm text-primary hover:underline flex items-center gap-1">
                            Ver todas
                            <PhArrowRight class="size-3" />
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentOrders.length === 0" class="text-sm text-muted-foreground text-center py-8">
                            Sem encomendas registadas.
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm">#{{ order.number }}</span>
                                        <Badge :variant="order.status === 'fechado' ? 'default' : 'secondary'">
                                            {{ order.status === 'fechado' ? 'Fechado' : 'Rascunho' }}
                                        </Badge>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ order.entity_name }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-sm">{{ formatCurrency(order.total) }}</div>
                                    <div class="text-xs text-muted-foreground">{{ formatDate(order.date) }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
