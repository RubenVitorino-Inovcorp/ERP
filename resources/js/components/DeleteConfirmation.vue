<script setup lang="ts">
import { PhWarning } from '@phosphor-icons/vue';
import { ref } from 'vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
    }>(),
    {
        title: 'Tem a certeza?',
        description: 'Esta ação é irreversível. O registo será permanentemente eliminado do sistema.',
        confirmLabel: 'Eliminar',
        cancelLabel: 'Cancelar',
    },
);

const emit = defineEmits<{
    confirm: [];
}>();

const open = ref(false);

function handleConfirm() {
    open.value = false;
    emit('confirm');
}
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogTrigger as-child>
            <!-- Permite slot custom para o trigger, com fallback para o botão padrão -->
            <slot name="trigger">
                <Button
                    variant="ghost"
                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 h-7 px-2.5"
                >
                    Eliminar
                </Button>
            </slot>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-500/10">
                        <PhWarning class="size-5 text-rose-600 dark:text-rose-400" weight="fill" />
                    </div>
                    <div>
                        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
                    </div>
                </div>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{ cancelLabel }}</AlertDialogCancel>
                <AlertDialogAction
                    class="bg-rose-600 text-white hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-700"
                    @click="handleConfirm"
                >
                    {{ confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
