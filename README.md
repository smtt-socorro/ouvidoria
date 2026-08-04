# Ouvidoria SMTT — ambiente local seguro

Esta cópia foi preparada a partir do backup recebido da antiga hospedagem. O sistema é HESK 3.4.6 em PHP, com MariaDB/MySQL.

## O que foi separado

- O código fica em `app/`.
- Banco, anexos e segredos ficam em `private/` e `.env`, ignorados pelo Git.
- `api/api.php` exige token; sem token, permanece desativada.
- A pasta `anexos` bloqueia acesso HTTP direto.
- O ambiente local usa PHP 8.2 + Apache e MariaDB 11.8.

## Pré-requisitos

- Docker Desktop com Docker Compose.
- Python 3 para preparar os dois ZIPs recebidos.
- Git para versionamento.

## 1. Criar o arquivo de ambiente

No PowerShell, dentro desta pasta:

```powershell
Copy-Item .env.example .env
notepad .env
```

Troque as senhas e a chave `HESK_URL_KEY`. Para o primeiro teste, mantenha:

```env
APP_URL=http://localhost:8080
FORCE_SSL=0
OUVIDORIA_API_TOKEN=
```

## 2. Preparar banco e anexos

Use os caminhos dos dois ZIPs originais:

```powershell
python .\scripts\prepare_private_data.py `
  "C:\caminho\ouvidoria.smttsocorro.com.br.zip" `
  "C:\caminho\backup-banco-ouvidoria.sql.zip"
```

A operação deve informar 155 arquivos de anexos e criar `private/database/001-ouvidoria.sql`.

## 3. Subir o sistema

```powershell
docker compose up -d --build
```

Acesse:

- Site: `http://localhost:8080`
- Administração: `http://localhost:8080/admin/`

A base recebida contém, como referência para validação:

- 453 chamados;
- 91 respostas;
- 7 usuários administrativos;
- 154 registros de anexos no banco e 155 arquivos na pasta, pois um deles é arquivo de bloqueio/índice.

## Comandos úteis

```powershell
# Ver contêineres
docker compose ps

# Ver logs
docker compose logs -f app
docker compose logs -f db

# Parar
docker compose down

# Apagar o banco local e importar novamente
docker compose down -v
docker compose up -d --build
```

O SQL só é importado quando o volume do banco está vazio.

## Versionar no GitHub

Crie um repositório **privado** e confira o que será enviado:

```powershell
git init
git add .
git status
git commit -m "Importa sistema da Ouvidoria em ambiente Docker"
git branch -M main
git remote add origin URL_DO_REPOSITORIO
git push -u origin main
```

Antes do `commit`, confirme que não aparecem `.env`, arquivos `.sql` nem arquivos dentro de `private/anexos`.

## Pendências antes da produção

1. Confirmar todas as telas e o login no ambiente local.
2. Trocar senhas dos usuários administrativos e rotacionar antigas credenciais.
3. Descobrir se algum sistema realmente utiliza a API; se não utilizar, remover a pasta `api`.
4. Atualizar o HESK em uma branch separada, preservando as customizações visuais e de impressão.
5. Só depois configurar VPS, domínio, HTTPS, backup e cron.
