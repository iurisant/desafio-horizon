<script setup lang="ts">
import type { FormDataConvertible } from '@inertiajs/core';
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

const ddiOptions = [
    { value: '55', label: '+55 Brasil' },
    { value: '1', label: '+1 EUA / Canadá' },
    { value: '351', label: '+351 Portugal' },
    { value: '54', label: '+54 Argentina' },
    { value: '34', label: '+34 Espanha' },
    { value: '44', label: '+44 Reino Unido' },
    { value: '49', label: '+49 Alemanha' },
    { value: '33', label: '+33 França' },
    { value: '39', label: '+39 Itália' },
    { value: '86', label: '+86 China' },
];

const ddiOrdenadosPorTamanho = [...ddiOptions]
    .map((opcao) => opcao.value)
    .sort((a, b) => b.length - a.length);

/**
 * Divide o telefone salvo (ex.: "+5511987654321") em DDI e "DDD + número"
 * para popular o select e o input separadamente ao editar.
 */
const telefoneInicial = computed(() => {
    const digitos = (props.fornecedor?.telefone ?? '').replace(/\D/g, '');

    if (!digitos) {
        return { ddi: '55', numero: '' };
    }

    const ddi =
        ddiOrdenadosPorTamanho.find((prefixo) => digitos.startsWith(prefixo)) ??
        '55';

    return { ddi, numero: digitos.slice(ddi.length) };
});

/**
 * Recombina DDI + número no campo único `telefone` esperado pelo backend.
 */
function normalizarTelefone(
    dados: Record<string, FormDataConvertible>,
): Record<string, FormDataConvertible> {
    const { ddi, numero, ...resto } = dados;

    const ddiDigitos = typeof ddi === 'string' ? ddi.replace(/\D/g, '') : '55';
    const numeroDigitos =
        typeof numero === 'string' ? numero.replace(/\D/g, '') : '';

    return { ...resto, telefone: `+${ddiDigitos}${numeroDigitos}` };
}
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
                :transform="normalizarTelefone"
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
                        placeholder="12ABC34501DE35"
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
                    <div class="flex gap-2">
                        <Select name="ddi" :default-value="telefoneInicial.ddi">
                            <SelectTrigger
                                id="fornecedor-ddi"
                                class="w-36 shrink-0"
                            >
                                <SelectValue placeholder="DDI" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opcao in ddiOptions"
                                    :key="opcao.value"
                                    :value="opcao.value"
                                >
                                    {{ opcao.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input
                            id="fornecedor-telefone"
                            name="numero"
                            :default-value="telefoneInicial.numero"
                            placeholder="11987654321"
                            class="flex-1"
                            required
                        />
                    </div>
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
