<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    PhMagnifyingGlass,
    PhCaretUp,
    PhCaretDown,
    PhCaretLeft,
    PhCaretRight,
    PhCaretDoubleLeft,
    PhCaretDoubleRight,
    PhFunnel,
    PhX,
} from '@phosphor-icons/vue';
import {
    FlexRender,
    getCoreRowModel,
    useVueTable
    
} from '@tanstack/vue-table';
import type {ColumnDef} from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';

// --- Tipagem do paginador Laravel ---
export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface DataTableProps<T> {
    columns: ColumnDef<T, unknown>[];
    paginated: PaginatedData<T>;
    searchable?: boolean;
    searchPlaceholder?: string;
    filters?: {
        search?: string;
        sort?: string;
        direction?: 'asc' | 'desc';
        per_page?: number;
    };
}

const props = withDefaults(
    defineProps<DataTableProps<Record<string, unknown>>>(),
    {
        searchable: true,
        searchPlaceholder: 'Pesquisar por nome, NIF ou contacto...',
        filters: () => ({
            search: '',
            sort: '',
            direction: 'asc',
            per_page: 15,
        }),
    },
);

// --- Estado reativo local ---
const search = ref(props.filters?.search ?? '');
const perPage = ref(String(props.filters?.per_page ?? 15));

const hasActiveFilters = computed(() => !!search.value);

// --- Navegação via Inertia (preservando estado SPA) ---
function visit(params: Record<string, string | number | undefined>) {
    const query: Record<string, string | number> = {};

    if (search.value) {
        query.search = search.value;
    }

    if (props.filters?.sort) {
        query.sort = props.filters.sort;
        query.direction = props.filters?.direction ?? 'asc';
    }

    Object.entries(params).forEach(([key, val]) => {
        if (val !== undefined && val !== '') {
            query[key] = val;
        }
    });

    router.get(window.location.pathname, query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// Pesquisa com debounce de 300ms
const debouncedSearch = useDebounceFn(() => {
    visit({ search: search.value, page: 1 });
}, 300);

watch(search, () => {
    debouncedSearch();
});

watch(perPage, (val) => {
    visit({ per_page: Number(val), page: 1 });
});

// Limpar todos os filtros
function clearFilters() {
    search.value = '';
    visit({ page: 1 });
}

// --- Ordenação ---
function toggleSort(columnId: string) {
    const currentSort = props.filters?.sort;
    const currentDir = props.filters?.direction ?? 'asc';

    if (currentSort === columnId) {
        visit({
            sort: columnId,
            direction: currentDir === 'asc' ? 'desc' : 'asc',
        });
    } else {
        visit({ sort: columnId, direction: 'asc' });
    }
}

// --- Paginação ---
function goToPage(page: number) {
    visit({ page });
}

// --- TanStack Table ---
const table = useVueTable({
    get data() {
        return props.paginated.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    manualSorting: true,
});

// Intervalo de páginas visíveis na paginação
const visiblePages = computed(() => {
    const current = props.paginated.current_page;
    const last = props.paginated.last_page;
    const delta = 2;
    const range: number[] = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    const pages: (number | '...')[] = [];

    if (last >= 1) {
pages.push(1);
}

    if (range.length > 0 && range[0] > 2) {
        pages.push('...');
    }

    pages.push(...range);

    if (range.length > 0 && range[range.length - 1] < last - 1) {
        pages.push('...');
    }

    if (last > 1) {
pages.push(last);
}

    return pages;
});
</script>

<template>
    <div class="space-y-4">
        <!-- Barra de filtros -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Pesquisa -->
            <div v-if="searchable" class="relative w-full max-w-xs">
                <PhMagnifyingGlass
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    :placeholder="searchPlaceholder"
                    class="h-9 rounded-lg border-transparent bg-white pl-9 shadow-sm transition-shadow focus-within:shadow-md dark:bg-white/5"
                />
            </div>

            <div class="flex flex-1 items-center justify-end gap-2">
                <!-- Slot para filtros adicionais (dropdowns, selects, etc.) -->
                <slot name="filters" />

                <!-- Limpar filtros -->
                <button
                    v-if="hasActiveFilters"
                    class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="clearFilters"
                >
                    <PhX class="size-3.5" weight="bold" />
                    Limpar filtros
                </button>
            </div>
        </div>

        <!-- Tabela -->
        <div
            class="overflow-hidden rounded-xl border bg-white shadow-sm dark:bg-white/[0.02]"
        >
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="border-b-0 bg-[var(--sidebar)] hover:bg-[var(--sidebar)]"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            class="h-11 text-xs font-semibold tracking-wider text-white/70 uppercase"
                            :class="{
                                'cursor-pointer transition-colors select-none hover:text-white':
                                    header.column.columnDef.enableSorting,
                            }"
                            @click="
                                header.column.columnDef.enableSorting
                                    ? toggleSort(header.column.id)
                                    : undefined
                            "
                        >
                            <div class="flex items-center gap-1.5">
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <template
                                    v-if="header.column.columnDef.enableSorting"
                                >
                                    <PhCaretUp
                                        v-if="
                                            filters?.sort ===
                                                header.column.id &&
                                            filters?.direction === 'asc'
                                        "
                                        class="size-3 text-white"
                                        weight="bold"
                                    />
                                    <PhCaretDown
                                        v-else-if="
                                            filters?.sort ===
                                                header.column.id &&
                                            filters?.direction === 'desc'
                                        "
                                        class="size-3 text-white"
                                        weight="bold"
                                    />
                                    <PhCaretUp
                                        v-else
                                        class="size-3 text-white/30"
                                    />
                                </template>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="transition-colors hover:bg-[#f8fafb] dark:hover:bg-white/[0.02]"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="py-3.5 text-sm"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableEmpty v-else :colspan="columns.length">
                        <div
                            class="flex flex-col items-center gap-2 py-12 text-sm text-muted-foreground"
                        >
                            <PhFunnel class="size-8 opacity-30" />
                            <p>Nenhum resultado encontrado.</p>
                            <button
                                v-if="hasActiveFilters"
                                class="text-xs text-primary underline underline-offset-2"
                                @click="clearFilters"
                            >
                                Limpar filtros
                            </button>
                        </div>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <!-- Rodapé: contagem + paginação + seletor por página -->
        <div class="flex items-center justify-between text-sm">
            <p class="text-muted-foreground">
                <template v-if="paginated.from && paginated.to">
                    Página {{ paginated.current_page }} de
                    {{ paginated.last_page }}
                </template>
                <template v-else> 0 resultados </template>
            </p>

            <!-- Paginação central -->
            <div v-if="paginated.last_page > 1" class="flex items-center gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    class="size-8"
                    :disabled="paginated.current_page === 1"
                    @click="goToPage(paginated.current_page - 1)"
                >
                    <PhCaretLeft class="size-4" />
                </Button>

                <template v-for="page in visiblePages" :key="page">
                    <span
                        v-if="page === '...'"
                        class="flex size-8 items-center justify-center text-xs text-muted-foreground"
                    >
                        …
                    </span>
                    <Button
                        v-else
                        :variant="
                            page === paginated.current_page
                                ? 'default'
                                : 'ghost'
                        "
                        size="icon"
                        class="size-8 text-xs font-medium"
                        @click="goToPage(page as number)"
                    >
                        {{ page }}
                    </Button>
                </template>

                <Button
                    variant="ghost"
                    size="icon"
                    class="size-8"
                    :disabled="paginated.current_page === paginated.last_page"
                    @click="goToPage(paginated.current_page + 1)"
                >
                    <PhCaretRight class="size-4" />
                </Button>
            </div>

            <!-- Seletor linhas por página -->
            <div class="flex items-center gap-2">
                <span class="whitespace-nowrap text-muted-foreground"
                    >Mostrar</span
                >
                <Select v-model="perPage">
                    <SelectTrigger class="h-8 w-[65px] text-xs">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent align="end">
                        <SelectItem value="10">10</SelectItem>
                        <SelectItem value="15">15</SelectItem>
                        <SelectItem value="25">25</SelectItem>
                        <SelectItem value="50">50</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
