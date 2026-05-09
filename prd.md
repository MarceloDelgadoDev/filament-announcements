# PRD — Filament Announcements

> **Versão:** 1.1.0-draft  
> **Autor:** A definir  
> **Data:** Maio 2026  
> **Status:** Em definição  
> **Compatibilidade:** Filament 5.x · Laravel 11+ · PHP 8.2+

---

## 1. Visão Geral

### 1.1 Resumo

**Filament Announcements** é um plugin para [FilamentPHP v5](https://filamentphp.com/) que permite exibir alertas e avisos institucionais na dashboard do painel administrativo. Os alertas são gerenciados via CRUD nativo do Filament, com suporte a expiração automática, níveis de severidade e dismiss por usuário.

### 1.2 Problema que resolve

Equipes que utilizam painéis Filament frequentemente precisam comunicar avisos operacionais aos usuários internos — manutenções programadas, descontinuação de funcionalidades, alertas de segurança — sem ter uma forma nativa de fazer isso dentro do próprio painel. A solução atual é comunicar via e-mail ou chat externo, perdendo o contexto da ferramenta.

### 1.3 Proposta de valor

- **Zero configuração obrigatória** — funciona out-of-the-box após instalação
- **Nativo ao ecossistema Filament v5** — UI, componentes e padrões 100% consistentes com o painel
- **Extensível** — suporte opcional a Spatie Permissions e Filament Shield
- **Leve** — sem dependências além do próprio Filament, Laravel e Spatie Package Tools

---

## 2. Público-alvo

| Perfil | Descrição |
|---|---|
| Desenvolvedor Laravel/Filament | Instala e configura o plugin em projetos próprios ou de clientes |
| Administrador do sistema | Gerencia os alertas via CRUD no painel |
| Usuário final do painel | Visualiza e dispensa alertas na dashboard |

---

## 3. Funcionalidades

### 3.1 CRUD de Alertas (AnnouncementResource)

Um Resource Filament completo para gerenciar os alertas.

**Campos do modelo `Announcement`:**

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `title` | string | Sim | Título curto do alerta |
| `body` | text | Sim | Descrição completa do aviso |
| `type` | enum | Sim | `info`, `warning`, `danger`, `success` |
| `is_active` | boolean | Sim | Ativa/desativa manualmente |
| `is_dismissible` | boolean | Sim | Define se o usuário pode dispensar |
| `starts_at` | datetime | Não | Data/hora de início de exibição |
| `expires_at` | datetime | Não | Data/hora de expiração automática |
| `created_at` | timestamp | Auto | — |
| `updated_at` | timestamp | Auto | — |

**Funcionalidades do Resource:**

- Listagem com badge colorido por tipo
- Filtro por tipo e status (ativo/expirado)
- Indicador visual de alertas expirados na tabela
- Formulário com DateTimePicker para `starts_at` e `expires_at`
- Toggle rápido de `is_active` direto na tabela

> **Nota v5:** O formulário usa a nova API de **Schemas** do Filament v5, que unifica Forms e Infolists em uma única API (`Schema` em vez de `Form`).

### 3.2 Widget na Dashboard (AnnouncementsWidget)

Um widget Livewire que exibe os alertas ativos na dashboard do Filament.

**Comportamento:**

- Exibe apenas alertas onde `is_active = true`, `starts_at <= now()` e (`expires_at IS NULL` ou `expires_at > now()`)
- Ordenação por `type` (danger primeiro) e depois por `created_at` desc
- Visual diferenciado por tipo usando as cores semânticas do Filament:
  - `danger` → vermelho
  - `warning` → amarelo/laranja
  - `info` → azul
  - `success` → verde
- Atualização automática via polling configurável (padrão: 60 segundos)

**Dismiss por usuário:**

- Se `is_dismissible = true`, exibe botão de fechar (×) no alerta
- Ao fechar, registra na tabela pivot `announcement_user` (`announcement_id`, `user_id`, `dismissed_at`)
- O alerta dispensado não aparece mais para aquele usuário
- Dismiss é reativo: feito via Livewire sem reload de página

### 3.3 Expiração Automática

- Alertas com `expires_at` preenchido param de aparecer automaticamente quando a data passa
- Nenhum job ou command necessário — a query de exibição já filtra por data
- Opcionalmente: command `announcements:prune` para limpar alertas expirados antigos do banco

### 3.4 Permissões

**Comportamento padrão (sem pacote externo):**

- O `AnnouncementResource` fica visível para todos os usuários que têm acesso ao painel
- O desenvolvedor pode sobrescrever via policy padrão do Laravel

**Configuração no `config/announcements.php`:**

```php
'permission_check' => null, // null = sem restrição adicional
// ou
'permission_check' => 'manage-announcements', // gate do Laravel
// ou
'permission_check' => fn($user) => $user->hasRole('admin'), // closure
```

**Integração com Spatie Permissions / Filament Shield:**

- Se o projeto usar Spatie, basta configurar `permission_check` com o nome da permissão
- O plugin não instala nem depende do Spatie Permissions — integração é opt-in via config

---

## 4. Arquitetura Técnica

### 4.1 Estrutura do pacote

```
filament-announcements/
├── config/
│   └── announcements.php
├── database/
│   └── migrations/
│       ├── create_announcements_table.php
│       └── create_announcement_user_table.php
├── resources/
│   └── views/
│       └── widgets/
│           └── announcements-widget.blade.php
├── src/
│   ├── AnnouncementsPlugin.php           # implements Filament\Contracts\Plugin
│   ├── AnnouncementsServiceProvider.php  # extends PackageServiceProvider (Spatie)
│   ├── Models/
│   │   └── Announcement.php
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── AnnouncementResource.php
│   │   │   └── AnnouncementResource/
│   │   │       └── Pages/
│   │   │           ├── ListAnnouncements.php
│   │   │           ├── CreateAnnouncement.php
│   │   │           └── EditAnnouncement.php
│   │   └── Widgets/
│   │       └── AnnouncementsWidget.php
│   └── Traits/
│       └── HasAnnouncements.php          # Trait para o model User
├── composer.json
└── README.md
```

### 4.2 Plugin Class (Filament v5)

O Filament v5 mantém a interface `Filament\Contracts\Plugin` com os métodos `getId()`, `register()` e `boot()`. O padrão recomendado inclui os métodos estáticos `make()` e `get()` para instanciação fluente e acesso global à configuração:

```php
<?php

namespace VendorName\Announcements;

use Filament\Contracts\Plugin;
use Filament\Panel;
use VendorName\Announcements\Filament\Resources\AnnouncementResource;
use VendorName\Announcements\Filament\Widgets\AnnouncementsWidget;

class AnnouncementsPlugin implements Plugin
{
    protected string $pollingInterval = '60s';

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'announcements';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([AnnouncementResource::class])
            ->widgets([AnnouncementsWidget::class]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function pollingInterval(string $interval): static
    {
        $this->pollingInterval = $interval;
        return $this;
    }

    public function getPollingInterval(): string
    {
        return $this->pollingInterval;
    }
}
```

### 4.3 ServiceProvider (Filament v5)

> **Mudança crítica v5:** O `PluginServiceProvider` foi **depreciado e removido**. O ServiceProvider agora deve estender `PackageServiceProvider` do `spatie/laravel-package-tools`, com uma propriedade estática `$name` obrigatória. Assets (CSS, JS, Alpine components) são registrados exclusivamente no método `packageBooted()`.

```php
<?php

namespace VendorName\Announcements;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AnnouncementsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'announcements';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                'create_announcements_table',
                'create_announcement_user_table',
            ])
            ->hasViews();
    }

    public function packageBooted(): void
    {
        // Registrar assets (CSS, JS, Alpine components) aqui
        // conforme a API de assets do Filament v5
    }
}
```

### 4.4 Registro pelo usuário (Filament v5)

```php
// AppPanelProvider.php do projeto que instala o plugin
use VendorName\Announcements\AnnouncementsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            AnnouncementsPlugin::make()
                ->pollingInterval('120s') // opcional
        );
}
```

### 4.5 Schemas API (Filament v5)

O Filament v5 introduziu **Schemas** como API unificada que substitui a antiga API separada de `Form` e `Infolist`. O formulário do Resource usará `Schema` como type-hint:

```php
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('title')->required(),
        Textarea::make('body')->required(),
        Select::make('type')
            ->options([
                'info'    => 'Info',
                'warning' => 'Warning',
                'danger'  => 'Danger',
                'success' => 'Success',
            ])
            ->required(),
        Toggle::make('is_active'),
        Toggle::make('is_dismissible'),
        DateTimePicker::make('starts_at'),
        DateTimePicker::make('expires_at'),
    ]);
}
```

### 4.6 Migrations

**`announcements`:**
```
id, title, body, type (enum), is_active, is_dismissible,
starts_at (nullable), expires_at (nullable),
created_at, updated_at
```

**`announcement_user` (pivot):**
```
announcement_id (FK), user_id (FK), dismissed_at
PRIMARY KEY (announcement_id, user_id)
```

### 4.7 Compatibilidade

| Dependência | Versão mínima |
|---|---|
| PHP | **8.2+** |
| Laravel | **11+** |
| Filament | **5.x** |
| Livewire | 3.x |
| spatie/laravel-package-tools | ^1.16 |

---

## 5. Inicialização do projeto (Filament Plugin Skeleton)

A documentação oficial do Filament v5 recomenda usar o [Filament Plugin Skeleton](https://github.com/filamentphp/plugin-skeleton) para iniciar o pacote:

```bash
# 1. Acessar o repositório e clicar em "Use this template"
# 2. Clonar o repositório gerado
# 3. Rodar o configurador interativo
php ./configure.php
```

O script faz perguntas interativas (nome do vendor, nome do plugin, namespace etc.) e gera toda a estrutura do pacote já configurada para Filament v5, incluindo o `ServiceProvider` correto com `PackageServiceProvider` e a propriedade `$name`.

---

## 6. Instalação (fluxo esperado pelo usuário final)

```bash
composer require vendor/filament-announcements

php artisan vendor:publish --tag="announcements-migrations"
php artisan migrate

php artisan vendor:publish --tag="announcements-config"  # opcional
php artisan vendor:publish --tag="announcements-views"   # opcional, para customizar o widget
```

Registrar o plugin no `PanelProvider`:

```php
->plugin(AnnouncementsPlugin::make())
```

Adicionar o trait ao model `User`:

```php
use VendorName\Announcements\Traits\HasAnnouncements;

class User extends Authenticatable
{
    use HasAnnouncements;
}
```

---

## 7. UX & Design

### 7.1 Widget

- Usa componentes nativos do Filament v5 (`<x-filament::badge>`, ícones Heroicons)
- Cada alerta é exibido como um card/banner com: ícone do tipo, título em negrito, corpo, e botão × se dismissível
- Visual limpo, sem dependência de CSS externo

### 7.2 Formulário de criação

- DateTimePicker nativo do Filament para `starts_at` e `expires_at`
- Select com cores indicativas para o campo `type`
- Toggle para `is_active` e `is_dismissible`
- Hint text explicando o comportamento de cada campo
- Usa a Schema API do Filament v5

### 7.3 Tabela de listagem

- Badge colorido na coluna `type`
- Coluna `expires_at` com status "Expirado" em vermelho quando a data já passou
- Toggle inline para `is_active`
- Ação bulk de ativar/desativar em lote

---

## 8. Publicação

### 8.1 Packagist

- Namespace sugerido: `vendor-name/filament-announcements`
- Licença: MIT
- Tags: `filament`, `filament-plugin`, `filament-v5`, `laravel`, `dashboard`, `announcements`, `alerts`

### 8.2 Filament Plugins Directory

- Submeter em [filamentphp.com/plugins](https://filamentphp.com/plugins)
- Requer: README completo, screenshots, link do repositório, compatibilidade declarada (Filament 5.x)

### 8.3 README (seções obrigatórias)

- Badges: versão, downloads, licença, compatibilidade Filament 5.x
- Screenshot do widget em ação
- Instalação passo a passo
- Tabela de todas as opções de configuração
- Screenshots do CRUD
- Seção de contribuição
- Changelog

---

## 9. Roadmap pós-lançamento (v2)

| Feature | Prioridade | Notas |
|---|---|---|
| Segmentação por grupo de usuários | Alta | Exibir alerta só para roles específicas |
| Suporte a ícone customizado por alerta | Média | Heroicon picker no form |
| Suporte a link/CTA no alerta | Média | Campo URL opcional com botão no widget |
| Suporte a multi-panel | Média | Widget/Resource em qualquer painel registrado |
| Command `announcements:prune` | Baixa | Limpar expirados antigos |
| Notificação Filament ao criar alerta | Baixa | Broadcast para usuários online |

---

## 10. Critérios de Aceite (MVP)

- [ ] Migration publica e executa sem erros
- [ ] CRUD de alertas aparece no painel após registrar o plugin
- [ ] Widget exibe apenas alertas ativos e dentro do período
- [ ] Alerta expirado para de aparecer automaticamente
- [ ] Dismiss funciona sem reload de página e persiste entre sessões
- [ ] Alerta não dismissível não exibe botão de fechar
- [ ] Config `permission_check` restringe acesso ao Resource quando definida
- [ ] `vendor:publish` exporta config e views corretamente
- [ ] ServiceProvider estende `PackageServiceProvider` com `$name` estático obrigatório
- [ ] Plugin class implementa `Filament\Contracts\Plugin` com `getId()`, `register()` e `boot()`
- [ ] Formulário usa Schema API do Filament v5 (`Schema` em vez de `Form`)
- [ ] Instalação funciona em projeto Filament v5 limpo em menos de 5 minutos
- [ ] README cobre 100% das opções de configuração

---

## 11. Diferenças v3 → v5 relevantes para este plugin

| Aspecto | Filament v3 | Filament v5 |
|---|---|---|
| ServiceProvider base | `PluginServiceProvider` (removido) | `PackageServiceProvider` (Spatie) com `$name` estático obrigatório |
| API de formulários | `form(Form $form): Form` | `form(Schema $schema): Schema` (API unificada) |
| Registro de assets | `packageBooted()` | `packageBooted()` (única forma correta) |
| Plugin interface | `Filament\Contracts\Plugin` | `Filament\Contracts\Plugin` (mesma) |
| `make()` / `get()` | Padrão recomendado | Padrão recomendado (mesma convenção) |
| Requisito PHP | 8.1+ | **8.2+** |
| Requisito Laravel | 10+ | **11+** |
| Skeleton oficial | github.com/filamentphp/plugin-skeleton | github.com/filamentphp/plugin-skeleton (atualizado para v5) |