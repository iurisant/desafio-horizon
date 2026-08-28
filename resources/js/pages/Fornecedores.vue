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
import FornecedorController from '@/actions/App/Http/Controllers/FornecedorController';
import ConfirmActionDialog from '@/components/ConfirmActionDialog.vue';
import FornecedorFormDialog from '@/components/FornecedorFormDialog.vue';
import Pagination from '@/components/Pagination.vue';
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
import { fornecedorAcoesVisiveis } from '@/lib/fornecedorActions';
import { fornecedores as fornecedoresRoute } from '@/routes';
import type { Fornecedor, FornecedorFiltros, Paginator } from '@/types';

const props = defineProps<{
    fornecedores: Paginator<Fornecedor>;
    filtros: FornecedorFiltros;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Fornecedores',
                href: fornecedoresRoute(),
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
        FornecedorController.index.url(),
        {
            busca: buscaDebounced.value || undefined,
            status: status.value !== 'todos' ? status.value : undefined,
            excluidos: excluidos.value ? 1 : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['fornecedores', 'filtros'],
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
            only: ['fornecedores'],
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

const linhas = computed(() =>
    props.fornecedores.data.map((fornecedor) => ({
        fornecedor,
        acoes: fornecedorAcoesVisiveis(fornecedor),
    })),
);
</script>

<template>
    <Head title="Fornecedores" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold">Fornecedores</h1>
            <FornecedorFormDialog mode="criar">
                <Button>
                    <Plus />
                    Novo fornecedor
                </Button>
            </FornecedorFormDialog>
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
                        <TableHead>CNPJ</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Telefone</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Produtos vinculados</TableHead>
                        <TableHead class="text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableSkeletonRows v-if="loading" :columns="7" />
                    <template v-else>
                        <TableRow v-if="linhas.length === 0">
                            <TableCell
                                colspan="7"
                                class="py-6 text-center text-muted-foreground"
                            >
                                Nenhum fornecedor encontrado.
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-for="linha in linhas"
                            :key="linha.fornecedor.id"
                        >
                            <TableCell>{{ linha.fornecedor.nome }}</TableCell>
                            <TableCell>{{ linha.fornecedor.cnpj }}</TableCell>
                            <TableCell>{{ linha.fornecedor.email }}</TableCell>
                            <TableCell>{{
                                linha.fornecedor.telefone
                            }}</TableCell>
                            <TableCell
                                ><StatusBadge :status="linha.fornecedor.status"
                            /></TableCell>
                            <TableCell>{{
                                linha.fornecedor.produtos_count ?? 0
                            }}</TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <FornecedorFormDialog
                                        v-if="linha.acoes.editar"
                                        mode="editar"
                                        :fornecedor="linha.fornecedor"
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
                                    </FornecedorFormDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.inativar"
                                        :form="
                                            FornecedorController.inativar.form(
                                                linha.fornecedor,
                                            )
                                        "
                                        title="Inativar fornecedor"
                                        confirm-label="Inativar"
                                    >
                                        <template #default>
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
                                        </template>
                                        <template #body>
                                            <p>
                                                Tem certeza que deseja inativar
                                                <strong>{{
                                                    linha.fornecedor.nome
                                                }}</strong
                                                >? Os produtos já cadastrados
                                                para este fornecedor
                                                permanecerão inalterados, mas
                                                nenhum produto novo poderá ser
                                                vinculado a ele enquanto estiver
                                                inativo.
                                            </p>
                                            <p class="mt-2">
                                                Produtos atualmente vinculados:
                                                <strong>{{
                                                    linha.fornecedor
                                                        .produtos_count ?? 0
                                                }}</strong
                                                >.
                                            </p>
                                        </template>
                                    </ConfirmActionDialog>

                                    <ConfirmActionDialog
                                        v-if="linha.acoes.reativar"
                                        :form="
                                            FornecedorController.reativar.form(
                                                linha.fornecedor,
                                            )
                                        "
                                        title="Reativar fornecedor"
                                        description="Fornecedores reativados voltam a poder receber novos produtos."
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
                                            FornecedorController.destroy.form(
                                                linha.fornecedor,
                                            )
                                        "
                                        title="Excluir fornecedor"
                                        description="O fornecedor será movido para a lixeira e poderá ser restaurado depois."
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
                                            FornecedorController.restore.form(
                                                linha.fornecedor,
                                            )
                                        "
                                        title="Restaurar fornecedor"
                                        description="O fornecedor voltará a aparecer na listagem normal."
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
                                            FornecedorController.forceDestroy.form(
                                                linha.fornecedor,
                                            )
                                        "
                                        title="Excluir definitivamente"
                                        description="Esta ação é permanente e não pode ser desfeita. Todos os dados deste fornecedor serão perdidos."
                                        confirm-label="Excluir definitivamente"
                                        variant="destructive"
                                        error-key="fornecedor"
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
            :links="fornecedores.links"
            :from="fornecedores.from"
            :to="fornecedores.to"
            :total="fornecedores.total"
            @navigate="navigate"
        />
    </div>
</template>
