<script setup lang="ts">
import type { CheckboxRootEmits, CheckboxRootProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { Check } from "@lucide/vue"
import { reactiveOmit } from "@vueuse/core"
import { CheckboxIndicator, CheckboxRoot, useForwardPropsEmits } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<CheckboxRootProps & { 
  class?: HTMLAttributes["class"]
  checked?: boolean | 'indeterminate' | null
}>()
const emits = defineEmits<CheckboxRootEmits & {
  'update:checked': [value: boolean | 'indeterminate']
}>()

const delegatedProps = reactiveOmit(props, "class", "checked")

const forwarded = useForwardPropsEmits(delegatedProps, emits)

const computedModelValue = computed(() => {
  if (props.checked !== undefined) {
    return props.checked;
  }
  return props.modelValue;
})

function handleUpdateModelValue(val: any) {
  emits('update:modelValue', val);
  emits('update:checked', val);
}
import { computed } from "vue"
</script>

<template>
  <CheckboxRoot
    v-slot="slotProps"
    data-slot="checkbox"
    v-bind="forwarded"
    :model-value="computedModelValue"
    @update:model-value="handleUpdateModelValue"
    :class="
      cn('peer border-input data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 aria-invalid:border-destructive size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
         props.class)"
  >
    <CheckboxIndicator
      data-slot="checkbox-indicator"
      class="grid place-content-center text-current transition-none"
    >
      <slot v-bind="slotProps">
        <Check class="size-3.5" />
      </slot>
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
