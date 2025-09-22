# ProLine Upload System

Sistema de upload e processamento de arquivos JSON com Laravel + Vue 3, usando filas e monitoramento via Laravel Horizon.

## Tecnologias

- **Backend**: Laravel 11 + Sail (Docker)
- **Frontend**: Vue 3 + Vue Router + Tailwind CSS
- **Database**: MySQL
- **Queue**: Redis + Laravel Horizon
- **Containerização**: Docker + Docker Compose

## Como Rodar

### 1. Configuração Inicial

```bash
# Clone o repositório
cd proline-challenge

# Copie o arquivo de ambiente
cp .env.example .env
```

### 2. Configurar .env

O arquivo `.env` já está configurado com as credenciais padrão do Sail. Principais variáveis:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

QUEUE_CONNECTION=redis
REDIS_HOST=redis
```

### 3. Subir os Containers

```bash
# Instalar dependências (se necessário)
composer install

# Subir todos os serviços
./vendor/bin/sail up -d
```

### 4. Configurar Banco de Dados

```bash
# Rodar as migrações
./vendor/bin/sail artisan migrate
```

### 5. Acessar a Aplicação

- **Frontend**: http://localhost
- **Horizon**: http://localhost/horizon

## Estrutura dos Serviços Docker

- **laravel.test**: Aplicação principal Laravel
- **horizon**: Processamento de filas
- **scheduler**: Tarefas agendadas (schedule:work)
- **mysql**: Banco de dados MySQL
- **redis**: Cache e filas

## Funcionalidades

### Upload de Arquivos (/app/envio)
- Seleção de arquivo .json via drag & drop ou clique
- Validação de formato JSON
- Upload assíncrono com feedback visual
- Retorna ID do lote para acompanhamento

### Monitoramento (/app/status)
- Lista todos os uploads com status
- Detalhamento por upload com estatísticas
- Polling automático para atualização em tempo real
- Funcionalidade de reprocessamento para uploads com erro

### API Endpoints

```
POST /api/uploads              - Upload de arquivo JSON
GET  /api/uploads              - Lista todos os uploads
GET  /api/uploads/{id}         - Detalhes de um upload
POST /api/uploads/{id}/reprocess - Reprocessar upload com erro
```

## Processamento em Background

O sistema processa os seguintes tipos de dados:

- **TimeTrackingEvents**: Eventos de controle de tempo
- **StoreCheckins**: Check-ins de lojas
- **PriceSurveys**: Pesquisas de preço (Start/Register/Finish)
- **ShelfLife**: Dados de vida útil (Start/Register/Finish)
- **StockAvailability**: Disponibilidade de estoque (Start/Register/Finish)
- **Media**: Arquivos de mídia (Start/Buffer/Finish)

## Monitoramento

### Laravel Horizon
- Acesse http://localhost/horizon
- Visualize filas, jobs processados e estatísticas
- Monitore falhas e reprocessamentos

### Logs
- Jobs registram logs detalhados com contexto
- Erros são capturados e associados ao lote
- Rastreabilidade completa do processamento

## Status dos Uploads

- **pending**: Aguardando processamento
- **processing**: Em processamento
- **completed**: Concluído com sucesso
- **error**: Erro durante processamento

## Frontend (Vue 3)

### Estrutura
```
resources/js/
├── App.vue           # Componente principal
├── app.js           # Configuração Vue + Router
├── pages/
│   ├── Upload.vue   # Tela de upload
│   └── Status.vue   # Tela de status
└── bootstrap.js     # Configuração base
```

### Build
Os assets já estão buildados em `public/build/`. Para rebuildar:

```bash
npm install --legacy-peer-deps
npm run build
```

## Exemplo de JSON para Upload

```json
{
  "TimeTrackingEvents": [
    {
      "user_id": 1,
      "date": "2024-01-15",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "total_hours": 9.0,
      "activity_type": "work"
    }
  ],
  "StoreCheckins": [
    {
      "user_id": 1,
      "store_id": "store_001",
      "checkin_time": "2024-01-15T09:30:00Z",
      "checkout_time": "2024-01-15T16:30:00Z",
      "latitude": 40.7128,
      "longitude": -74.0060
    }
  ]
}
```

## Comandos Úteis

```bash
# Ver logs da aplicação
./vendor/bin/sail logs laravel.test

# Ver logs do Horizon
./vendor/bin/sail logs horizon

# Acessar container da aplicação
./vendor/bin/sail shell

# Parar todos os serviços
./vendor/bin/sail down
```