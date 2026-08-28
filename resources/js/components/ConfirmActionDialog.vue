<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { RouteFormDefinition } from '@/wayfinder';

const props = withDefaults(
    defineProps<{
        form: RouteFormDefinition<'get' | 'post' | 'put' | 'patch' | 'delete'>;
        title: string;
        description?: string;
        confirmLabel: string;
        cancelLabel?: string;
        variant?: 'default' | 'destructive';
        errorKey?: string;
        disabled?: boolean;
    }>(),
    {
        cancelLabel: 'Cancelar',
        variant: 'default',
    },
);

const open = ref(false);

function handleOpenChange(value: boolean) {
    if (props.disabled && value) {
        return;
    }

    open.value = value;
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="form"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing, reset, clearErrors }"
                @success="open = false"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription v-if="description">
                        {{ description }}
                    </DialogDescription>
                    <DialogDescription v-else class="sr-only">
                        Confirme a ação para continuar.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="$slots.body" class="text-sm text-muted-foreground">
                    <slot name="body" />
                </div>

                <InputError
                    v-if="errorKey"
                    :message="errors[errorKey]"
                    class="mt-2"
                />

                <DialogFooter class="mt-6 gap-2">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                }
                            "
                        >
                            {{ cancelLabel }}
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :variant="variant"
                        :disabled="processing"
                    >
                        {{ confirmLabel }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
