<script setup lang="ts">
import type { FormDataConvertible } from '@inertiajs/core';
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProdutoController from '@/actions/App/Http/Controllers/ProdutoController';
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
import { Textarea } from '@/components/ui/textarea';
import type { Fornecedor, Produto } from '@/types';

const props = defineProps<{
    mode: 'criar' | 'editar';
    produto?: Produto;
    fornecedoresElegiveis: Pick<Fornecedor, 'id' | 'nome'>[];
}>();

const open = ref(false);

const formConfig = computed(() =>
    props.mode === 'criar'
        ? ProdutoController.store.form()
        : ProdutoController.update.form(props.produto!),
);

const opcoesFornecedor = computed(() => {
    const fornecedorAtual = props.produto?.fornecedor;
    const jaElegivel = fornecedorAtual
        ? props.fornecedoresElegiveis.some((f) => f.id === fornecedorAtual.id)
        : true;

    if (fornecedorAtual && !jaElegivel) {
        return [
            ...props.fornecedoresElegiveis,
            {
                id: fornecedorAtual.id,
                nome: `${fornecedorAtual.nome} (inativo)`,
            },
        ];
    }

    return props.fornecedoresElegiveis;
});

function normalizarPreco(
    dados: Record<string, FormDataConvertible>,
): Record<string, FormDataConvertible> {
    const preco =
        typeof dados.preco === 'string' || typeof dados.preco === 'number'
            ? Number(dados.preco).toFixed(2)
            : dados.preco;

    return { ...dados, preco };
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
                :transform="normalizarPreco"
                :options="{ preserveScroll: true }"
                class="space-y-4"
                v-slot="{ errors, processing, reset, clearErrors }"
                @success="open = false"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{
                            mode === 'criar' ? 'Novo produto' : 'Editar produto'
                        }}
                    </DialogTitle>
                    <DialogDescription class="sr-only">
                        Formulário de cadastro e edição de produto.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="produto-fornecedor">Empresa</Label>
                    <Select
                        name="fornecedor_id"
                        :default-value="
                            produto?.fornecedor_id
                                ? String(produto.fornecedor_id)
                                : undefined
                        "
                    >
                        <SelectTrigger id="produto-fornecedor" class="w-full">
                            <SelectValue placeholder="Selecione a empresa" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="fornecedor in opcoesFornecedor"
                                :key="fornecedor.id"
                                :value="String(fornecedor.id)"
                            >
                                {{ fornecedor.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.fornecedor_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="produto-nome">Nome</Label>
                    <Input
                        id="produto-nome"
                        name="nome"
                        :default-value="produto?.nome"
                        required
                    />
                    <InputError :message="errors.nome" />
                </div>

                <div class="grid gap-2">
                    <Label for="produto-descricao">Descrição</Label>
                    <Textarea
                        id="produto-descricao"
                        name="descricao"
                        :default-value="produto?.descricao ?? undefined"
                    />
                    <InputError :message="errors.descricao" />
                </div>

                <div class="grid gap-2">
                    <Label for="produto-preco">Preço</Label>
                    <Input
                        id="produto-preco"
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="preco"
                        :default-value="produto?.preco"
                        required
                    />
                    <InputError :message="errors.preco" />
                </div>

                <div class="grid gap-2">
                    <Label for="produto-codigo-interno">Código interno</Label>
                    <Input
                        id="produto-codigo-interno"
                        name="codigo_interno"
                        :default-value="produto?.codigo_interno"
                        required
                    />
                    <InputError :message="errors.codigo_interno" />
                </div>

                <div class="grid gap-2">
                    <Label for="produto-status">Status</Label>
                    <Select
                        name="status"
                        :default-value="produto?.status ?? 'ativo'"
                    >
                        <SelectTrigger id="produto-status" class="w-full">
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
