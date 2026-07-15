# ERP InovCorp

Este é o sistema ERP desenvolvido para a InovCorp. O projeto foi construído para gerir clientes, fornecedores, propostas, encomendas, contas bancárias, arquivo digital e toda a parte financeira e de acessos de forma integrada.

## Stack
- Laravel 13
- Vue 3 + Inertia.js v2
- TailwindCSS v4
- Shadcn Vue + Radix Primitives
- MySQL

## Segurança
- Criptografia na base de dados (usando CipherSweet para dados sensíveis como NIF).
- Ficheiros e documentos guardados fora do diretório público (`storage/app/private` ou `local` disk) para garantir acesso apenas a utilizadores autenticados.
- Autenticação com suporte a 2FA (Two-Factor Authentication) através do Laravel Fortify.
- Proteção XSS, CSRF e SQL Injection nativa.

## Módulos Disponíveis
- **Entidades:** Clientes e Fornecedores partilham a mesma estrutura com filtros dinâmicos e validação/integração com o VIES.
- **Contactos:** Gestão de contactos associados a entidades.
- **Propostas e Encomendas:** Fluxo completo desde rascunho até à conversão em encomendas de clientes e fornecedores. Geração de PDFs customizados.
- **Financeiro:** Contas bancárias, contas correntes e faturação de fornecedores (com fluxo de pagamento e envio de comprovativos por email).
- **Arquivo Digital:** Repositório centralizado de todos os documentos gerados ou anexados no sistema.
- **Calendário:** Agendamento de atividades integrado (FullCalendar) com filtros por utilizador e entidade.
- **Gestão de Acessos:** Controlo de utilizadores e permissões modulares (Spatie Permission).
- **Logs:** Registo de auditoria de ações críticas no sistema.

## Instalação

### Pré-requisitos
- PHP 8.5+
- Composer
- Node.js & npm
- Servidor MySQL

### Passos para configurar localmente

1. Clonar o repositório:
```bash
git clone https://github.com/RubenVitorino-Inovcorp/ERP.git
cd erp-inovcorp
```

2. Instalar as dependências do PHP e do Frontend:
```bash
composer install
npm install
```

3. Duplicar o ficheiro de ambiente e configurar as variáveis da base de dados e de mail (Mailpit):
```bash
cp .env.example .env
```
*(Não esquecer de gerar a chave da aplicação com `php artisan key:generate`)*

4. Correr as migrações e popular a base de dados:
```bash
php artisan migrate --seed
```

5. Iniciar o servidor de desenvolvimento do Vite e o queue worker para os emails em background:
```bash
# Num terminal
npm run dev

# Noutro terminal
php artisan queue:listen
```

*Nota: Se a aplicação for executada através do Laravel Herd, o servidor web e o PHP já são geridos automaticamente. Basta assegurar que a base de dados (MySQL) e o Mailpit estão ativos no painel do Herd.*
