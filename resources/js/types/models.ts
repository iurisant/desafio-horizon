export type StatusFornecedor = 'ativo' | 'inativo';
export type StatusProduto = 'ativo' | 'inativo';

export type Fornecedor = {
    id: number;
    nome: string;
    cnpj: string;
    email: string;
    telefone: string;
    status: StatusFornecedor;
    produtos_count?: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

export type Produto = {
    id: number;
    fornecedor_id: number;
    fornecedor?: Pick<Fornecedor, 'id' | 'nome' | 'status'>;
    nome: string;
    descricao: string | null;
    preco: string;
    codigo_interno: string;
    status: StatusProduto;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
    links: PaginationLink[];
};

export type FornecedorFiltros = {
    busca: string | null;
    status: StatusFornecedor | null;
    excluidos: boolean;
};

export type ProdutoFiltros = {
    busca: string | null;
    status: StatusProduto | null;
    excluidos: boolean;
};

export type DashboardFiltros = {
    status: StatusProduto | null;
    data_inicio: string | null;
    data_fim: string | null;
};

export type DashboardCards = {
    fornecedores_ativos: number;
    fornecedores_inativos: number;
    produtos_total: number;
    produtos_excluidos: number;
    valor_total_produtos: string;
    preco_medio_produtos: string;
};

export type ContagemPorStatus = {
    status: 'ativo' | 'inativo';
    total: number;
};

export type ProdutoPorDia = {
    dia: string;
    total: number;
};

export type TopFornecedor = {
    id: number;
    nome: string;
    produtos_count: number;
};

export type DashboardMetricas = {
    cards: DashboardCards;
    fornecedoresPorStatus: ContagemPorStatus[];
    produtosPorStatus: ContagemPorStatus[];
    produtosPorDia: ProdutoPorDia[];
    topFornecedores: TopFornecedor[];
};
