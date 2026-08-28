<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArchiveRestore,
    Ban,
    Pencil,
    Plus,
    Power,
    Search,
    Trash2,
} from '@lucide/vue';
import { refDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import ProdutoController from '@/actions/App/Http/Controllers/ProdutoController';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import Pagination from '@/components/Pagination.vue';
import ProdutoFormDialog from '@/components/ProdutoFormDialog.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import TableSkeletonRows from '@/components/TableSkeletonRows.vue';
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
} from '@/components/ui/table';
import { produtoAcoesVisiveis } from '@/lib/produtoActions';
import { produtos as produtosRoute } from '@/routes';
import type { Fornecedor, Paginator, Produto, ProdutoFiltros } from '@/types';

const props = defineProps<{
    produtos: Paginator<Produto>;
    filtros: ProdutoFiltros;
    fornecedoresElegiveis: Pick<Fornecedor, 'id' | 'nome'>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Produtos',
                href: produtosRoute(),
            },
        ],
    },
});

const busca = ref(props.filtros.busca ?? '');
const buscaDebounced = refDebounced(busca, 400);
const status = ref(props.filtros.status ?? 'todos');
const excluidos = ref(props.filtros.excluidos);
const loading = ref(false);

function reload() {
    router.get(
        ProdutoController.index.url(),
        {
            busca: buscaDebounced.value || undefined,
            status: status.value !== 'todos' ? status.value : undefined,
            excluidos: excluidos.value ? 1 : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['produtos', 'filtros'],
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

watch([buscaDebounced, status, excluidos], reload);

function navigate(url: string) {
    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['produtos'],
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function formatarPreco(preco: string): string {
    return Number(preco).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}

const linhas = computed(() =>
    props.produtos.data.map((produto) => ({
        produto,
        acoes: produtoAcoesVisiveis(produto),
    })),
);
</script>

<template>
    <Head title="Produtos" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold">Produtos</h1>
            <ProdutoFormDialog
                mode="criar"
                :fornecedores-elegiveis="fornecedoresElegiveis"
            >
                <Button>
                    <Plus />
                    Novo produto
                </Button>
            </ProdutoFormDialog>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative sm:max-w-xs sm:flex-1">
                <Search
                    class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="busca"
                    placeholder="Buscar por nome"
                    class="pl-8"
                />
            </div>

            <Select v-model="status">
                <SelectTrigger class="w-full sm:w-44">
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="todos">Todos os status</SelectItem>
                    <SelectItem value="ativo">Ativo</SelectItem>
                    <SelectItem value="inativo">Inativo</SelectItem>
                </SelectContent>
            </Select>

            <div class="flex gap-1">
                <Button
                    type="button"
                    size="sm"
                    :variant="!excluidos ? 'default' : 'outline'"
                    @click="excluidos = false"
                >
                    Ativos
                </Button>
                <Button
                    type="button"
                    size="sm"
                    :variant="excluidos ? 'default' : 'outline'"
                    @click="excluidos = true"
                >
                    Excluídos
                </Button>
            </div>
        </div>

        <div
            class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nome</TableHead>
                        <TableHead>Empresa</TableHead>
                        <TableHead>Código interno</TableHead>
                        <TableHead>Preço</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableSkeletonRows v-if="loading" :columns="6" />
                    <template v-else>
                        <TableRow v-if="linhas.length === 0">
                            <TableCell
                                colspan="6"
                                class="py-6 text-center text-muted-foreground"
                            >
                                Nenhum produto encontrado.
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-for="linha in linhas"
                            :key="linha.produto.id"
                        >
                            <TableCell>{{ linha.produto.nome }}</TableCell>
                            <TableCell>{{
                                linha.produto.fornecedor?.nome
                            }}</TableCell>
                            <TableCell>{{
                                linha.produto.codigo_interno
                            }}</TableCell>
                            <TableCell>{{
                                formatarPreco(linha.produto.preco)
                            }}</TableCell>
                            <TableCell
                                ><StatusBadge :status="linha.produto.status"
                            /></TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <ProdutoFormDialog
                                        v-if="linha.acoes.editar"
                                        mode="editar"
                                        :produto="linha.produto"
                                        :fornecedores-elegiveis="
                                            fornecedoresElegiveis
                                        "
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title="Editar"
                                        >
                                            <Pencil />
                                            <span class="sr-only">Editar</span>
                                        </Button>
                                    </ProdutoFormDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.inativar"
                                        :form="
                                            ProdutoController.inativar.form(
                                                linha.produto,
                                            )
                                        "
                                        title="Inativar produto"
                                        description="O produto deixará de ser exibido como disponível."
                                        confirm-label="Inativar"
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title="Inativar"
                                        >
                                            <Ban />
                                            <span class="sr-only"
                                                >Inativar</span
                                            >
                                        </Button>
                                    </ConfirmActionDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.reativar"
                                        :form="
                                            ProdutoController.reativar.form(
                                                linha.produto,
                                            )
                                        "
                                        title="Reativar produto"
                                        description="O produto voltará a ficar disponível."
                                        confirm-label="Reativar"
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title="Reativar"
                                        >
                                            <Power />
                                            <span class="sr-only"
                                                >Reativar</span
                                            >
                                        </Button>
                                    </ConfirmActionDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.excluir"
                                        :form="
                                            ProdutoController.destroy.form(
                                                linha.produto,
                                            )
                                        "
                                        title="Excluir produto"
                                        description="O produto será movido para a lixeira e poderá ser restaurado depois."
                                        confirm-label="Excluir"
                                        variant="destructive"
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title="Excluir"
                                        >
                                            <Trash2 />
                                            <span class="sr-only">Excluir</span>
                                        </Button>
                                    </ConfirmActionDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.restaurar"
                                        :form="
                                            ProdutoController.restore.form(
                                                linha.produto,
                                            )
                                        "
                                        title="Restaurar produto"
                                        description="O produto voltará a aparecer na listagem normal."
                                        confirm-label="Restaurar"
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title="Restaurar"
                                        >
                                            <ArchiveRestore />
                                            <span class="sr-only"
                                                >Restaurar</span
                                            >
                                        </Button>
                                    </ConfirmActionDialog>

                                    <ConfirmActionDialog
                                        v-if="
                                            linha.acoes.excluirDefinitivamente
                                        "
                                        :form="
                                            ProdutoController.forceDestroy.form(
                                                linha.produto,
                                            )
                                        "
                                        title="Excluir definitivamente"
                                        description="Esta ação é permanente e não pode ser desfeita. Todos os dados deste produto serão perdidos."
                                        confirm-label="Excluir definitivamente"
                                        variant="destructive"
                                    >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            class="text-destructive hover:text-destructive"
                                            title="Excluir definitivamente"
                                        >
                                            <Trash2 />
                                            <span class="sr-only"
                                                >Excluir definitivamente</span
                                            >
                                        </Button>
                                    </ConfirmActionDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <Pagination
            :links="produtos.links"
            :from="produtos.from"
            :to="produtos.to"
            :total="produtos.total"
            @navigate="navigate"
        />
    </div>
</template>
