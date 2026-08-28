# Desafio Horizon

Aplicação web para cadastro e gestão de **Fornecedores** e **Produtos**, construída como um monólito Laravel + Inertia + Vue. Cada Produto pertence a um Fornecedor, com regras de negócio para inativação, exclusão lógica (soft delete), restauração e exclusão definitiva.

## Sumário

- [Sobre o projeto](#sobre-o-projeto)
- [Stack utilizada](#stack-utilizada)
- [Funcionalidades](#funcionalidades)
- [Regras de negócio](#regras-de-negócio)
- [Pré-requisitos](#pré-requisitos)
- [Como instalar](#como-instalar)
- [Como iniciar](#como-iniciar)
- [Como testar](#como-testar)
- [Documentação da API (Swagger)](#documentação-da-api-swagger)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Observações importantes](#observações-importantes)

## Sobre o projeto

O app tem duas telas principais — **Fornecedores** e **Produtos** — com listagem paginada, filtro por nome e status, acesso à lixeira (registros excluídos) e um conjunto de ações por registro (editar, inativar, reativar, excluir, restaurar, excluir definitivamente) exibidas apenas quando a regra de negócio realmente permite a ação.

Todas as mutações dão feedback visual via toast, atualizam a listagem sem reload de página (Inertia) e exibem erros de validação e de regra de negócio em linguagem simples, direto no formulário/diálogo que originou a ação.

## Stack utilizada

**Backend**
- PHP 8.3 + [Laravel 13](https://laravel.com/)
- [Inertia.js v3](https://inertiajs.com/) (adapter Laravel) — SPA server-driven, sem API REST separada para as telas
- SQLite (banco padrão de desenvolvimento; qualquer driver suportado pelo Laravel funciona)
- [Laravel Fortify](https://laravel.com/docs/fortify) (scaffolding de autenticação do starter kit — autenticação real fica desativada neste projeto, veja [Observações](#observações-importantes))
- [Laravel Wayfinder](https://github.com/laravel/wayfinder) — gera funções TypeScript tipadas para rotas/controllers, usadas no frontend
- [Dedoc Scramble](https://scramble.dedoc.co/) — geração automática de documentação OpenAPI/Swagger a partir das rotas e `FormRequest`s

**Frontend**
- Vue 3 + TypeScript
- Tailwind CSS v4
- [Reka UI](https://reka-ui.com/) (componentes headless, estilo shadcn-vue)
- [vue-sonner](https://github.com/xiaoluoboding/vue-sonner) (toasts)
- [@vueuse/core](https://vueuse.org/) (ex.: debounce dos filtros)
- Vite

**Qualidade e testes**
- [Pest](https://pestphp.com/) / PHPUnit para testes de feature do backend
- [Larastan](https://github.com/larastan/larastan) (PHPStan) para análise estática
- [Laravel Pint](https://laravel.com/docs/pint) para formatação de código PHP
- ESLint + Prettier (`prettier-plugin-tailwindcss`) para o frontend
- `vue-tsc` para checagem de tipos TypeScript

## Funcionalidades

- **CRUD completo** de Fornecedores e Produtos, com validação de campos (CNPJ com dígito verificador, telefone com DDI/DDD, preço com exatamente 2 casas decimais, código interno único por fornecedor, etc.)
- **Listagem com paginação, busca por nome e filtro por status** (Ativo/Inativo)
- **Lixeira**: aba "Excluídos" com URL própria (compartilhável), mostrando só os registros com soft delete
- **Inativar / Reativar**: ação rápida e reversível, sem passar pelo formulário de edição completo
- **Excluir (soft delete) vs. Excluir definitivamente**: dois níveis de exclusão claramente diferenciados na interface, com textos de confirmação distintos (reversível vs. irreversível)
- **Ações condicionais**: a interface nunca oferece um botão que a regra de negócio recusaria depois (ex.: "excluir definitivamente" some quando o fornecedor ainda tem produtos vinculados)
- **Feedback visual** (toast) para toda ação de sucesso, e erros de regra de negócio exibidos inline, em português, sem jargão técnico
- **Documentação de API interativa** gerada automaticamente (Swagger/OpenAPI via Scramble)

## Regras de negócio

**Fornecedor**
| Campo | Regra |
| --- | --- |
| Nome | Obrigatório, 3 a 150 caracteres |
| CNPJ | Obrigatório, válido (dígito verificador) e único |
| Email | Obrigatório, formato válido e único |
| Telefone | Obrigatório, com DDI e DDD |
| Status | Restrito a `Ativo` ou `Inativo` |

- Exclusão lógica (soft delete) e restauração.
- Exclusão física **bloqueada** enquanto o fornecedor tiver produtos vinculados (mesmo que os produtos também estejam com soft delete).
- Ao inativar, os produtos já cadastrados não são afetados, mas nenhum produto novo pode ser vinculado a um fornecedor inativo.

**Produto**
| Campo | Regra |
| --- | --- |
| Empresa (fornecedor) | Obrigatória, precisa estar ativa e não excluída |
| Nome | Obrigatório, 3 a 150 caracteres |
| Descrição | Opcional, até 2.000 caracteres |
| Preço | Obrigatório, maior que zero, com exatamente 2 casas decimais |
| Código interno | Obrigatório, único **dentro da mesma empresa** (pode repetir entre fornecedores diferentes) |
| Status | Restrito a `Ativo` ou `Inativo` |

- Exclusão lógica (soft delete), restauração e exclusão física — a exclusão definitiva de um produto não tem restrição adicional.

## Pré-requisitos

- **PHP 8.3+** com as extensões padrão do Laravel (`mbstring`, `pdo_sqlite`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer 2**
- **Node.js 20+** e **npm**
- Um driver de banco suportado pelo Laravel — o projeto já vem configurado para **SQLite** (nenhuma instalação de servidor de banco é necessária)

## Como instalar

```bash
# 1. Clonar o repositório
git clone https://github.com/iurisant/desafio-horizon.git
cd desafio-horizon

# 2. Instalar as dependências PHP
composer install

# 3. Copiar o .env e gerar a chave da aplicação
cp .env.example .env
php artisan key:generate

# 4. Criar o banco SQLite e rodar as migrations
touch database/database.sqlite
php artisan migrate

# 5. Instalar as dependências JS
npm install
```

> Alternativa: `composer run setup` roda `composer install`, copia o `.env`, gera a chave, executa as migrations e instala/builda as dependências JS em um único comando — só é preciso criar o `database.sqlite` (passo 4) antes, se ele ainda não existir.

## Como iniciar

Modo desenvolvimento (servidor PHP + Vite com hot reload):

```bash
composer run dev
```

Isso sobe o servidor Laravel (`php artisan serve`), o Vite em modo dev e o worker de filas simultaneamente. A aplicação fica disponível em `http://localhost:8000`.

Alternativa manual (dois terminais):

```bash
php artisan serve
npm run dev
```

Build de produção dos assets:

```bash
npm run build
```

> **Sem login necessário**: o middleware `AutoLogin` autentica automaticamente qualquer requisição com um usuário padrão — não é preciso criar conta nem fazer login para usar o app (ver [Observações](#observações-importantes)).

## Como testar

**Backend** (Pest/PHPUnit, cobre validação, regras de negócio, filtros e as transições de estado de Fornecedor/Produto):

```bash
php artisan test
```

**Análise estática (PHPStan/Larastan):**

```bash
vendor/bin/phpstan analyse
```

**Formatação PHP (Pint):**

```bash
vendor/bin/pint --test    # checar
vendor/bin/pint           # corrigir
```

**Frontend (lint, formatação e tipos):**

```bash
npm run lint:check      # ESLint
npm run format:check    # Prettier
npm run types:check     # vue-tsc
```

**Pipeline completo (o que roda no CI):**

```bash
composer ci:check
```

## Documentação da API (Swagger)

A documentação interativa (OpenAPI) das rotas de Fornecedor e Produto é gerada automaticamente a partir das rotas e das regras de validação (`FormRequest`s) — não é mantida manualmente.

Com a aplicação rodando, acesse:

```
http://localhost:8000/docs/api
```

Também disponível pelo menu lateral, em **Documentation**.

## Estrutura do projeto

```
app/
  Concerns/            # traits reutilizáveis (ex.: FlashesToastMessages)
  Enums/                # StatusFornecedor, StatusProduto
  Http/
    Controllers/        # FornecedorController, ProdutoController
    Requests/            # validação (Store/Update para cada entidade)
  Models/               # Fornecedor, Produto (Eloquent, soft delete)
  Rules/                # regras de validação customizadas (Cnpj, TelefoneComDdiDdd)
  Services/             # regras de negócio (FornecedorService, ProdutoService)
database/
  factories/            # factories para testes
  migrations/
resources/js/
  components/            # componentes Vue reutilizáveis (dialogs, tabela, paginação, etc.)
  lib/                    # funções puras (ex.: regras de visibilidade de ação por registro)
  pages/                  # páginas Inertia (Fornecedores.vue, Produtos.vue, ...)
  types/                  # tipos TypeScript compartilhados
tests/Feature/           # testes de feature (FornecedorTest, ProdutoTest, ...)
```

## Observações importantes

- **Projeto de desafio/demo**: o middleware `App\Http\Middleware\AutoLogin` autentica automaticamente qualquer requisição com um usuário padrão, então nenhuma página exige login manual. Isso é intencional para facilitar a avaliação do desafio, mas por consequência quebra os testes de autenticação padrão do starter kit (`AuthenticationTest`, `RegistrationTest`, `PasswordResetTest`, `TwoFactorChallengeTest`, entre outros) — são 13 testes pré-existentes, não relacionados às funcionalidades de Fornecedor/Produto, que falham por esse motivo esperado. Os testes de Fornecedor e Produto (58 no total) passam integralmente.
- **Banco de dados**: por padrão o projeto usa SQLite para simplicidade; para usar MySQL/PostgreSQL basta ajustar as variáveis `DB_*` no `.env`.
