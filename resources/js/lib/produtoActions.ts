import type { Produto } from '@/types';

export function produtoAcoesVisiveis(produto: Produto) {
    const excluido = produto.deleted_at !== null;

    return {
        editar: !excluido,
        inativar: !excluido && produto.status === 'ativo',
        reativar: !excluido && produto.status === 'inativo',
        excluir: !excluido,
        restaurar: excluido,
        excluirDefinitivamente: excluido,
    };
}
