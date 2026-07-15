<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { PhArrowLeft, PhFloppyDisk, PhPlus, PhTrash } from '@phosphor-icons/vue'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import AppLayout from '@/layouts/AppLayout.vue'
import * as clientRoutes from '@/routes/encomendas'
import * as supplierRoutes from '@/routes/supplier-orders'

const props = withDefaults(
    defineProps<{
        nextNumber: number
        clients: { id: number; name: string; nif: string }[]
        suppliers: { id: number; name: string }[]
        articles: { id: number; reference: string; name: string; price: number; vat_rate_id: number }[]
        vatRates: { id: number; name: string; value: number }[]
        type?: 'cliente' | 'fornecedor'
    }>(),
    {
        type: 'cliente',
    }
)

const isSupplier = computed(() => props.type === 'fornecedor')
const routes = computed(() => isSupplier.value ? supplierRoutes : clientRoutes)

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: isSupplier.value ? 'Encomendas Fornecedores' : 'Encomendas Clientes', href: isSupplier.value ? '/encomendas-fornecedores' : '/encomendas' },
    { title: 'Nova Encomenda', href: '#' },
])

const form = useForm({
    entity_id: '',
    status: 'rascunho',
    lines: [
        {
            article_id: '',
            supplier_id: '',
            cost_price: null as number | null,
            quantity: 1,
            unit_price: 0,
            vat_rate_id: props.vatRates[0]?.id ? String(props.vatRates[0].id) : '',
        },
    ],
})

function addLine() {
    form.lines.push({
        article_id: '',
        supplier_id: '',
        cost_price: null,
        quantity: 1,
        unit_price: 0,
        vat_rate_id: props.vatRates[0]?.id ? String(props.vatRates[0].id) : '',
    })
}

function removeLine(index: number) {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

function onArticleChange(index: number, articleIdStr: string) {
    const articleId = Number(articleIdStr)
    const article = props.articles.find(a => a.id === articleId)

    if (article) {
        form.lines[index].article_id = articleIdStr
        form.lines[index].unit_price = Number(article.price)

        if (article.vat_rate_id) {
            form.lines[index].vat_rate_id = String(article.vat_rate_id)
        }
    }
}

const totals = computed(() => {
    let subtotal = 0
    let vatTotal = 0

    form.lines.forEach((line) => {
        const qty = Number(line.quantity) || 0
        const price = Number(line.unit_price) || 0
        const vatObj = props.vatRates.find(v => String(v.id) === String(line.vat_rate_id))
        const vatPercent = vatObj ? Number(vatObj.value) : 0

        const lineSubtotal = qty * price
        const lineVat = lineSubtotal * (vatPercent / 100)

        subtotal += lineSubtotal
        vatTotal += lineVat
    })

    return {
        subtotal: subtotal.toFixed(2),
        vatTotal: vatTotal.toFixed(2),
        grandTotal: (subtotal + vatTotal).toFixed(2),
    }
})

function submit() {
    form.post(routes.value.store.url())
}
</script>

<template>
    <Head :title="isSupplier ? 'Nova Encomenda Fornecedor' : 'Nova Encomenda'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                        {{ isSupplier ? 'Nova Encomenda Fornecedor #' + nextNumber : 'Nova Encomenda #' + nextNumber }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isSupplier ? 'Preencha os dados da encomenda a fornecedor e as respetivas linhas de artigos.' : 'Preencha os dados da encomenda e as respetivas linhas de artigos.' }}
                    </p>
                </div>
                <Link :href="routes.index.url()">
                    <Button variant="outline" class="gap-2">
                        <PhArrowLeft class="size-4" />
                        Voltar
                    </Button>
                </Link>
            </div>

            <!-- Formulário -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Cabeçalho da Encomenda -->
                <div class="rounded-xl border bg-card p-6 shadow-sm grid grid-cols-1 gap-6 md:grid-cols-2">

                    <!-- Entidade (Cliente/Fornecedor) -->
                    <FormField name="entity_id">
                        <FormItem>
                            <FormLabel>{{ isSupplier ? 'Fornecedor' : 'Cliente' }} <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.entity_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.entity_id }">
                                        <SelectValue :placeholder="isSupplier ? 'Selecione um fornecedor' : 'Selecione um cliente'" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="String(client.id)"
                                        >
                                            {{ client.name }} ({{ client.nif }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.entity_id">{{ form.errors.entity_id }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Estado -->
                    <FormField name="status">
                        <FormItem>
                            <FormLabel>Estado <span class="text-destructive">*</span></FormLabel>
                            <FormControl>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="rascunho">Rascunho</SelectItem>
                                        <SelectItem value="fechado">Fechado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage v-if="form.errors.status">{{ form.errors.status }}</FormMessage>
                        </FormItem>
                    </FormField>

                </div>

                <!-- Tabela de Linhas de Artigos -->
                <div class="rounded-xl border bg-card p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-foreground">Linhas de Artigos</h2>
                        <Button type="button" variant="outline" size="sm" class="gap-2" @click="addLine">
                            <PhPlus class="size-4" />
                            Adicionar Linha
                        </Button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="p-3 min-w-[250px]">Artigo (Ref / Nome) <span class="text-destructive">*</span></th>
                                    <th class="p-3 min-w-[200px]">Fornecedor</th>
                                    <th class="p-3 w-28 min-w-[120px]">P. Custo (€)</th>
                                    <th class="p-3 w-24 min-w-[100px]">Qtd <span class="text-destructive">*</span></th>
                                    <th class="p-3 w-32 min-w-[120px]">P. Venda (€) <span class="text-destructive">*</span></th>
                                    <th class="p-3 w-32 min-w-[120px]">IVA <span class="text-destructive">*</span></th>
                                    <th class="p-3 w-32 min-w-[120px] text-right">Total (€)</th>
                                    <th class="p-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="(line, index) in form.lines" :key="index" class="hover:bg-muted/30">

                                    <!-- Artigo -->
                                    <td class="p-2">
                                        <Select :model-value="line.article_id" @update:model-value="(val) => onArticleChange(index, val)">
                                            <SelectTrigger :class="{ 'border-destructive': form.errors[`lines.${index}.article_id`] }">
                                                <SelectValue placeholder="Selecione um artigo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="art in articles"
                                                    :key="art.id"
                                                    :value="String(art.id)"
                                                >
                                                    [{{ art.reference }}] {{ art.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </td>

                                    <!-- Fornecedor -->
                                    <td class="p-2">
                                        <Select v-model="line.supplier_id">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Nenhum" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="sup in suppliers"
                                                    :key="sup.id"
                                                    :value="String(sup.id)"
                                                >
                                                    {{ sup.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </td>

                                    <!-- Preço Custo -->
                                    <td class="p-2">
                                        <Input
                                            type="number"
                                            step="0.01"
                                            v-model.number="line.cost_price"
                                            placeholder="0.00"
                                        />
                                    </td>

                                    <!-- Quantidade -->
                                    <td class="p-2">
                                        <Input
                                            type="number"
                                            min="1"
                                            v-model.number="line.quantity"
                                            :class="{ 'border-destructive': form.errors[`lines.${index}.quantity`] }"
                                        />
                                    </td>

                                    <!-- Preço Unitário Venda -->
                                    <td class="p-2">
                                        <Input
                                            type="number"
                                            step="0.01"
                                            v-model.number="line.unit_price"
                                            :class="{ 'border-destructive': form.errors[`lines.${index}.unit_price`] }"
                                        />
                                    </td>

                                    <!-- IVA -->
                                    <td class="p-2">
                                        <Select v-model="line.vat_rate_id">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="vat in vatRates"
                                                    :key="vat.id"
                                                    :value="String(vat.id)"
                                                >
                                                    {{ vat.name }} ({{ vat.value }}%)
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </td>

                                    <!-- Total Linha -->
                                    <td class="p-2 text-right font-medium">
                                        {{
                                            (
                                                (line.quantity || 0) *
                                                (line.unit_price || 0) *
                                                (1 + (Number(vatRates.find(v => String(v.id) === String(line.vat_rate_id))?.value || 0) / 100))
                                            ).toFixed(2)
                                        }} €
                                    </td>

                                    <!-- Ação Remover -->
                                    <td class="p-2 text-center">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="text-muted-foreground hover:text-destructive"
                                            :disabled="form.lines.length === 1"
                                            @click="removeLine(index)"
                                        >
                                            <PhTrash class="size-4" />
                                        </Button>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumo Financeiro -->
                    <div class="flex justify-end pt-4">
                        <div class="w-full max-w-xs space-y-2 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Subtotal:</span>
                                <span>{{ totals.subtotal }} €</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Total IVA:</span>
                                <span>{{ totals.vatTotal }} €</span>
                            </div>
                            <div class="flex justify-between font-semibold text-lg text-foreground border-t pt-2">
                                <span>Total Final:</span>
                                <span>{{ totals.grandTotal }} €</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer do Form -->
                <div class="flex items-center justify-end gap-4">
                    <Link :href="routes.index.url()">
                        <Button type="button" variant="ghost">Cancelar</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="gap-2">
                        <PhFloppyDisk class="size-4" />
                        Gravar Encomenda
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
