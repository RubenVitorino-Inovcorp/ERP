<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { toast } from 'vue-sonner'
import SearchSelect from '@/components/SearchSelect.vue'
import type { SelectOption } from '@/components/SearchSelect.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'

const props = defineProps<{
    event?: any
    users: any[]
    entities: any[]
    types: any[]
    actions: any[]
}>()

const emit = defineEmits(['saved', 'cancelled'])

const isEditing = !!props.event?.id

const form = useForm({
    date: props.event?.date || '',
    time: props.event?.time || '',
    duration: props.event?.duration || '',
    entity_id: props.event?.entity_id?.toString() || '',
    calendar_type_id: props.event?.calendar_type_id?.toString() || '',
    calendar_action_id: props.event?.calendar_action_id?.toString() || '',
    description: props.event?.description || '',
    status: props.event?.status || 'agendado',
    partilha: props.event?.partilha || [],
    conhecimento: props.event?.conhecimento || [],
})

// Opções
const userOptions = computed<SelectOption[]>(() => props.users.map(u => ({ label: u.name, value: u.id })))
const entityOptions = computed<SelectOption[]>(() => props.entities.map(e => ({ label: e.name, value: e.id })))
const typeOptions = computed<SelectOption[]>(() => props.types.map(t => ({ label: t.name, value: t.id })))
const actionOptions = computed<SelectOption[]>(() => props.actions.map(a => ({ label: a.name, value: a.id })))

// Single selects
const selectedEntity = computed({
    get: () => entityOptions.value.find(o => o.value == Number(form.entity_id)) || null,
    set: (val: any) => {
 form.entity_id = val ? val.value.toString() : '' 
},
})

const selectedType = computed({
    get: () => typeOptions.value.find(o => o.value == Number(form.calendar_type_id)) || null,
    set: (val: any) => {
 form.calendar_type_id = val ? val.value.toString() : '' 
},
})

const selectedAction = computed({
    get: () => actionOptions.value.find(o => o.value == Number(form.calendar_action_id)) || null,
    set: (val: any) => {
 form.calendar_action_id = val ? val.value.toString() : '' 
},
})

// Multi selects
const selectedPartilha = computed({
    get: () => userOptions.value.filter(o => form.partilha.includes(o.value)),
    set: (val: any[]) => {
 form.partilha = val.map((v: any) => v.value) 
},
})

const selectedConhecimento = computed({
    get: () => userOptions.value.filter(o => form.conhecimento.includes(o.value)),
    set: (val: any[]) => {
 form.conhecimento = val.map((v: any) => v.value) 
},
})

function submit() {
    if (isEditing) {
        form.put((window as any).route('calendar-events.update', props.event.id), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Evento atualizado com sucesso.')
                emit('saved')
            },
            onError: () => {
                toast.error('Verifique os erros no formulário.')
            }
        })
    } else {
        form.post((window as any).route('calendar-events.store'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Evento agendado com sucesso.')
                emit('saved')
            },
            onError: () => {
                toast.error('Verifique os erros no formulário.')
            }
        })
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-6 mt-4">
        
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="date">Data <span class="text-destructive">*</span></Label>
                <Input id="date" type="date" v-model="form.date" required />
                <p v-if="form.errors.date" class="text-xs text-destructive">{{ form.errors.date }}</p>
            </div>
            
            <div class="space-y-2">
                <Label for="time">Hora <span class="text-destructive">*</span></Label>
                <Input id="time" type="time" v-model="form.time" required />
                <p v-if="form.errors.time" class="text-xs text-destructive">{{ form.errors.time }}</p>
            </div>
        </div>
        
        <div class="space-y-2">
            <Label for="duration">Duração (minutos)</Label>
            <Input id="duration" type="number" min="0" v-model="form.duration" />
            <p v-if="form.errors.duration" class="text-xs text-destructive">{{ form.errors.duration }}</p>
        </div>

        <div class="space-y-2">
            <Label>Entidade Associada</Label>
            <SearchSelect v-model="selectedEntity" :options="entityOptions" placeholder="Pesquisar entidade..." />
            <p v-if="form.errors.entity_id" class="text-xs text-destructive">{{ form.errors.entity_id }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label>Tipo de Evento</Label>
                <SearchSelect v-model="selectedType" :options="typeOptions" placeholder="Pesquisar tipo..." />
                <p v-if="form.errors.calendar_type_id" class="text-xs text-destructive">{{ form.errors.calendar_type_id }}</p>
            </div>

            <div class="space-y-2">
                <Label>Acção</Label>
                <SearchSelect v-model="selectedAction" :options="actionOptions" placeholder="Pesquisar acção..." />
                <p v-if="form.errors.calendar_action_id" class="text-xs text-destructive">{{ form.errors.calendar_action_id }}</p>
            </div>
        </div>

        <div class="space-y-2">
            <Label>Partilhar com:</Label>
            <SearchSelect v-model="selectedPartilha" :options="userOptions" multiple placeholder="Pesquisar utilizadores..." />
            <p v-if="form.errors.partilha" class="text-xs text-destructive">{{ form.errors.partilha }}</p>
        </div>

        <div class="space-y-2">
            <Label>CC:</Label>
            <SearchSelect v-model="selectedConhecimento" :options="userOptions" multiple placeholder="Pesquisar utilizadores..." />
            <p v-if="form.errors.conhecimento" class="text-xs text-destructive">{{ form.errors.conhecimento }}</p>
        </div>

        <div class="space-y-2">
            <Label for="description">Descrição</Label>
            <Textarea id="description" rows="3" v-model="form.description" />
            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
        </div>

        <div class="space-y-2">
            <Label for="status">Estado</Label>
            <Select v-model="form.status">
                <SelectTrigger>
                    <SelectValue placeholder="Selecione o estado..." />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="agendado">Agendado</SelectItem>
                    <SelectItem value="realizado">Realizado</SelectItem>
                    <SelectItem value="cancelado">Cancelado</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
        </div>

        <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
            <Button type="button" variant="outline" @click="emit('cancelled')">Cancelar</Button>
            <Button type="submit" :disabled="form.processing">
                {{ isEditing ? 'Guardar Alterações' : 'Criar Evento' }}
            </Button>
        </div>
    </form>
</template>
