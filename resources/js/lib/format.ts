export function formatarPreco(preco: string | number): string {
    return Number(preco).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}
