<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();

const company = computed(() => (page.props as any).company);
const companyName = computed(() => company.value?.name || 'ERP InovCorp');
const logoUrl = computed(() => company.value?.logo_path ? `/storage/${company.value.logo_path}` : null);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground overflow-hidden"
    >
        <img
            v-if="logoUrl"
            :src="logoUrl"
            :alt="companyName"
            class="size-full object-contain"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ companyName }}</span>
    </div>
</template>
