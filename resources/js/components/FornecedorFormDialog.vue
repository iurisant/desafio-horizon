<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FornecedorController from '@/actions/App/Http/Controllers/FornecedorController';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Fornecedor } from '@/types';

const props = defineProps<{
    mode: 'criar' | 'editar';
    fornecedor?: Fornecedor;
}>();

const open = ref(false);

const formConfig = computed(() =>
    props.mode === 'criar'
        ? FornecedorController.store.form()
        : FornecedorController.update.form(props.fornecedor!),
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="formConfig"
                reset-on-success
                :options="{ preserveScroll: true }"
                class="space-y-4"
                v-slot="{ errors, processing, reset, clearErrors }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{
                            mode === 'criar'
                                ? 'Novo fornecedor'
                                : 'Editar fornecedor'
                        }}
                    </DialogTitle>
                    <DialogDescription class="sr-only">
                        Formulário de cadastro e edição de fornecedor.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="fornecedor-nome">Nome</Label>
                    <Input
                        id="fornecedor-nome"
                        name="nome"
                        :default-value="fornecedor?.nome"
                        required
                    />
                    <InputError :message="errors.nome" />
                </div>

                <div class="grid gap-2">
                    <Label for="fornecedor-cnpj">CNPJ</Label>
                    <Input
                        id="fornecedor-cnpj"
                        name="cnpj"
                        :default-value="fornecedor?.cnpj"
                        placeholder="00000000000000"
                        required
                    />
                    <InputError :message="errors.cnpj" />
                </div>

                <div class="grid gap-2">
                    <Label for="fornecedor-email">Email</Label>
                    <Input
                        id="fornecedor-email"
                        type="email"
                        name="email"
                        :default-value="fornecedor?.email"
                        required
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="fornecedor-telefone">Telefone</Label>
                    <Input
                        id="fornecedor-telefone"
                        name="telefone"
                        :default-value="fornecedor?.telefone"
                        placeholder="+5511987654321"
                        required
                    />
                    <InputError :message="errors.telefone" />
                </div>

                <div class="grid gap-2">
                    <Label for="fornecedor-status">Status</Label>
                    <Select
                        name="status"
                        :default-value="fornecedor?.status ?? 'ativo'"
                    >
                        <SelectTrigger id="fornecedor-status" class="w-full">
                            <SelectValue placeholder="Selecione o status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="ativo">Ativo</SelectItem>
                            <SelectItem value="inativo">Inativo</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.status" />
                </div>

                <DialogFooter class="gap-2">
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
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ mode === 'criar' ? 'Cadastrar' : 'Salvar' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
