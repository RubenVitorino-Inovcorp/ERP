<script setup lang="ts">
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

export interface SelectOption {
    label: string
    value: string | number
}

withDefaults(defineProps<{
    options: SelectOption[]
    multiple?: boolean
    placeholder?: string
    clearable?: boolean
    closeOnSelect?: boolean
    disabled?: boolean
}>(), {
    multiple: false,
    placeholder: 'Pesquisar...',
    clearable: true,
    disabled: false,
})

const model = defineModel<SelectOption | SelectOption[] | null>()
</script>

<template>
    <vSelect
        v-model="model"
        :options="options"
        :multiple="multiple"
        :close-on-select="closeOnSelect ?? !multiple"
        :placeholder="placeholder"
        :clearable="clearable"
        :disabled="disabled"
        class="search-select"
    />
</template>

<style>
.search-select .vs__dropdown-toggle {
    border: 1px solid var(--border);
    border-radius: calc(var(--radius) - 2px);
    padding: 0.375rem 0.5rem;
    min-height: 2.5rem;
    background: transparent;
    font-size: 0.875rem;
}

.search-select .vs__dropdown-toggle:hover {
    border-color: var(--ring);
}

.search-select.vs--open .vs__dropdown-toggle {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in oklch, var(--ring) 25%, transparent);
}

.search-select .vs__search::placeholder {
    color: var(--muted-foreground);
}

.search-select .vs__search,
.search-select .vs__search:focus {
    margin: 0;
    padding: 0;
    font-size: 0.875rem;
    color: var(--foreground);
}

/* Tags dos itens selecionados (multi-select) */
.search-select .vs__selected {
    background: var(--primary);
    color: var(--primary-foreground);
    border: none;
    border-radius: calc(var(--radius) - 4px);
    padding: 0.125rem 0.5rem;
    margin: 0.125rem 0.25rem 0.125rem 0;
    font-size: 0.75rem;
    font-weight: 500;
}

.search-select .vs__deselect {
    fill: var(--primary-foreground);
    margin-left: 0.25rem;
}

.search-select .vs__deselect:hover {
    opacity: 0.7;
}

/* Dropdown de opções */
.search-select .vs__dropdown-menu {
    border: 1px solid var(--border);
    border-radius: calc(var(--radius) - 2px);
    background: var(--popover);
    color: var(--popover-foreground);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    padding: 0.25rem;
    font-size: 0.875rem;
}

.search-select .vs__dropdown-option {
    padding: 0.375rem 0.5rem;
    border-radius: calc(var(--radius) - 4px);
}

.search-select .vs__dropdown-option--highlight {
    background: var(--accent);
    color: var(--accent-foreground);
}

.search-select .vs__clear,
.search-select .vs__open-indicator {
    fill: var(--muted-foreground);
}

.search-select .vs__no-options {
    color: var(--muted-foreground);
    font-size: 0.875rem;
    padding: 0.5rem;
}

/* Estado desativado */
.search-select.vs--disabled .vs__dropdown-toggle {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
