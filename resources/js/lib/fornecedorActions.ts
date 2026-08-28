import type { Fornecedor } from '@/types';

export function fornecedorAcoesVisiveis(fornecedor: Fornecedor) {
    const excluido = fornecedor.deleted_at !== null;

    return {
        editar: !excluido,
        inativar: !excluido && fornecedor.status === 'ativo',
        reativar: !excluido && fornecedor.status === 'inativo',
        excluir: !excluido,
        restaurar: excluido,
        excluirDefinitivamente:
            excluido && (fornecedor.produtos_count ?? 0) === 0,
    };
}
