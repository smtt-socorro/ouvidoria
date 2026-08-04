# Observações de segurança da migração

## Corrigido nesta cópia

- Credenciais do banco removidas do código e movidas para `.env`.
- API pública passou a exigir token e não mostra erros internos do banco.
- Acesso HTTP direto à pasta de anexos foi bloqueado.
- Banco, anexos, caches e backups foram excluídos do versionamento.

## Itens que ainda exigem ação

- O backup contém credenciais antigas em `hesk_settings.inc.php` e `api/api.php`; considere-as comprometidas e não as reutilize.
- O banco contém dados pessoais, relatos, endereços IP e contas administrativas. Restrinja acesso e backups.
- O HESK 3.4.6 deve ser usado apenas para restaurar e validar. Faça a atualização em uma cópia antes da publicação.
- Revise a necessidade da view `ouvidoria_api_view`, que expõe dados pessoais para a API.
- Altere todas as senhas administrativas após confirmar acesso.
