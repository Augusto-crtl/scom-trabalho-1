# Metal Archives - Trabalho Individual II (SCOM)

Plataforma web para a comunidade Heavy Metal desenvolvida para a disciplina de Sistemas Corporativos e Mídia (SCOM). O projeto evoluiu da interface estática (Trabalho I) para uma aplicação cliente-servidor completa com backend em PHP, persistência em MySQL, autenticação por sessão, controle de acesso por perfis (RBAC), logs de auditoria e testes automatizados.

---

## 🛠️ Tecnologias Utilizadas

* **Frontend:** HTML5, CSS3 (design gótico e responsivo), JavaScript ES6+ (assíncrono com `fetch` API).
* **Backend:** PHP 8+ utilizando a extensão PDO.
* **Banco de Dados:** MySQL / MariaDB.
* **Servidor Local:** XAMPP (Apache + MySQL).

---

## 📁 Arquitetura do Projeto

O projeto adota uma estrutura em camadas simples para separar a interface gráfica, as rotas da API RESTful e a conexão com o banco de dados:

```text
metal-archives/
├── .env                       # Credenciais e configurações locais
├── database.sql               # Script DDL/DML de criação do banco
├── index.html                 # Página principal da aplicação
├── README.md                  # Documentação técnica do repositório
├── style.css                  # Estilos globais e componentes da interface
├── script.js                  # Lógica do cliente, requisições Fetch e eventos DOM
├── Imagens/                   # Recursos visuais e mídias estáticas
└── backend/
    ├── config/
    │   ├── database.php       # Conexão PDO e leitura do .env
    │   ├── auth_check.php     # Controle de sessões e verificação de perfil
    │   └── rate_limit.php     # Middleware para limitação de tentativas de login
    ├── tests/
    │   └── test_api.php       # Suíte de testes automatizados via CLI
    └── api/
        ├── admin/
        │   └── dashboard.php  # Métricas do sistema e consulta de logs
        ├── auth/
        │   ├── login.php      # Autenticação de usuário
        │   ├── register.php   # Cadastro de Headbangers e Bandas
        │   ├── logout.php     # Encerramento de sessão
        │   └── me.php         # Verificação do estado do usuário logado
        └── bandas/
            ├── listar.php     # Feed de bandas com busca, filtro e paginação
            ├── minha_banda.php# Dados da banda do usuário logado
            ├── salvar.php     # Criação e edição do perfil da banda
            ├── deletar.php    # Exclusão do registro da banda
            ├── curtir.php     # Sistema de Hails (curtidas)
            └── comentar.php   # Envio de comentários nos cards
```

---

## 🚀 Como Executar Localmente

### 1. Pré-requisitos
* **XAMPP** (ou servidor Apache + MySQL equivalente) com suporte a **PHP 8.0** ou superior.

### 2. Instalação do Projeto
1. Baixe ou clone os arquivos da aplicação no diretório `htdocs` do seu XAMPP:
   `C:\xampp\htdocs\metal-archives`
2. Certifique-se de manter a estrutura de arquivos idêntica à descrita na arquitetura.

### 3. Configuração do Banco de Dados
1. Inicie os módulos **Apache** e **MySQL** pelo XAMPP Control Panel.
2. Acesse a interface do phpMyAdmin no navegador: `http://localhost/phpmyadmin`
3. Crie um novo banco de dados com o nome exato: `metal_archives`.
4. Com o banco `metal_archives` selecionado, navegue até a aba **SQL** ou **Importar** e execute o conteúdo do arquivo `database.sql` (disponível na raiz do projeto).

### 4. Configuração do Arquivo `.env`
Na raiz do projeto, certifique-se de que o arquivo `.env` está configurado de acordo com as credenciais do seu servidor local:

```ini
DB_HOST=127.0.0.1
DB_NAME=metal_archives
DB_USER=root
DB_PASS=
```

### 5. Acesso à Aplicação
Com o servidor XAMPP ativo, abra o navegador e acesse:
`http://localhost/metal-archives/index.html`

---

## 🧪 Testes Automatizados

A aplicação inclui uma suíte de testes automatizados desenvolvida em PHP para validar os endpoints e as regras de negócio pela linha de comando.

### Como Executar
1. Abra o Terminal ou Prompt de Comando (CMD).
2. Navegue até o diretório raiz do projeto:
   ```cmd
   cd C:\xampp\htdocs\metal-archives
   ```
3. Execute o comando:
   ```cmd
   php backend/tests/test_api.php
   ```

### Cenários Cobertos
* **Conexão PDO:** Valida se a aplicação consegue estabelecer comunicação com o MySQL utilizando as variáveis do `.env`.
* **Validação de Payload:** Garante que tentativas de requisição sem os campos obrigatórios retornem código HTTP `400 Bad Request`.
* **Autenticação Incorreta:** Confirma se tentativas de login com senhas inválidas retornam HTTP `401 Unauthorized`.
* **Listagem Pública:** Verifica se a rota de bandas retorna HTTP `200 OK` e a estrutura de dados JSON correta sem exigir login.
* **Controle de Acesso (RBAC):** Assegura que tentativas de acessar rotas restritas (como o Painel Administrativo) sem autenticação apropriada retornem HTTP `401` ou `403`.

---

## 🔒 Segurança Implementada

* **Prepared Statements (PDO):** 100% das consultas ao banco de dados utilizam binds parametrizados para impedir vulnerabilidades de SQL Injection.
* **Sanitização contra XSS:** Todas as variáveis e dados inseridos dinamicamente na página passam por tratamento de escape de entidades HTML (`escapeHTML()`).
* **Criptografia de Senhas:** O cadastro gera hashes de senha com o algoritmo `BCRYPT` (`password_hash`), e as autenticações validam o hash através do `password_verify()`.
* **Rate Limiting:** Bloqueia conexões que acumulem 5 tentativas malsucedidas de login no período de 15 minutos, registrando o incidente na tabela `logs_sistema`.
* **Segurança de Sessão:** A autenticação utiliza cookies com restrição `HttpOnly` e renovação automática do ID de sessão (`session_regenerate_id`) no momento do login para prevenir sequestro de sessão (Session Fixation).
* **Controle de Acesso RBAC:** Todas as rotas do backend verificam as permissões do usuário logado (`visitante`, `headbanger`, `banda` ou `admin`) antes de processar qualquer ação.

---

## 📖 Documentação dos Endpoints

### 1. Autenticação

* **`POST /backend/api/auth/register.php`**
  * **Descrição:** Cadastra um novo usuário no sistema (Headbanger ou Banda).
  * **Exemplo de Corpo (JSON):**
    ```json
    {
      "fname": "Viktor Vance",
      "email": "viktor@metal.com",
      "senha": "senhaSegura123",
      "genero_id": "M",
      "tipo_perfil": "headbanger",
      "banda_favorita": "Black Sabbath"
    }
    ```
  * **Códigos de Status:** `201 Created`, `400 Bad Request`, `409 Conflict`.

* **`POST /backend/api/auth/login.php`**
  * **Descrição:** Valida as credenciais do usuário e cria a sessão HTTP.
  * **Exemplo de Corpo (JSON):**
    ```json
    {
      "email": "viktor@metal.com",
      "senha": "senhaSegura123"
    }
    ```
  * **Códigos de Status:** `200 OK`, `400 Bad Request`, `401 Unauthorized`, `429 Too Many Requests`.

* **`GET /backend/api/auth/me.php`**
  * **Descrição:** Retorna os dados do usuário atualmente autenticado.
  * **Códigos de Status:** `200 OK`.

* **`POST /backend/api/auth/logout.php`**
  * **Descrição:** Destrói a sessão ativa do usuário e limpa os cookies.
  * **Códigos de Status:** `200 OK`.

---

### 2. Gestão de Bandas e Feed

* **`GET /backend/api/bandas/listar.php`**
  * **Descrição:** Lista as bandas registradas. Permite busca textual, filtro por subgênero e paginação de resultados.
  * **Parâmetros da URL:** `pagina` (int), `limite` (int), `busca` (string), `subgenero` (string).
  * **Exemplo:** `/backend/api/bandas/listar.php?pagina=1&limite=6&subgenero=thrash`
  * **Códigos de Status:** `200 OK`.

* **`POST /backend/api/bandas/salvar.php`**
  * **Descrição:** Salva ou edita o perfil de banda associado à conta logada.
  * **Permissão:** Perfil `banda` ou `admin`.
  * **Exemplo de Corpo (JSON):**
    ```json
    {
      "nome_banda": "Soul King",
      "subgenero_banda": "heavy",
      "link_media": "https://spotify.com",
      "biografia": "Biografia e história da banda."
    }
    ```
  * **Códigos de Status:** `200 OK`, `401 Unauthorized`, `403 Forbidden`.

* **`DELETE /backend/api/bandas/deletar.php`**
  * **Descrição:** Remove permanentemente o registro da banda do usuário logado.
  * **Permissão:** Perfil `banda` ou `admin`.
  * **Códigos de Status:** `200 OK`, `401 Unauthorized`, `403 Forbidden`.

---

### 3. Interações e Painel Admin

* **`POST /backend/api/bandas/curtir.php`**
  * **Descrição:** Adiciona ou remove a curtida (Hail) do usuário em uma banda.
  * **Exemplo de Corpo (JSON):** `{"banda_id": 1}`
  * **Códigos de Status:** `200 OK`, `401 Unauthorized`.

* **`POST /backend/api/bandas/comentar.php`**
  * **Descrição:** Envia um comentário para a página/card de uma banda.
  * **Exemplo de Corpo (JSON):** `{"banda_id": 1, "texto": "Excelente sonoridade!"}`
  * **Códigos de Status:** `201 Created`, `400 Bad Request`, `401 Unauthorized`.

* **`GET /backend/api/admin/dashboard.php`**
  * **Descrição:** Retorna as estatísticas do fórum e os registros da tabela de auditoria `logs_sistema`.
  * **Permissão:** Perfil `admin`.
  * **Códigos de Status:** `200 OK`, `401 Unauthorized`, `403 Forbidden`.