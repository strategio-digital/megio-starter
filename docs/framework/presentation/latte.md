---
layout: 'page'
uri: '/presentation/latte'
position: 1
slug: 'presentation-latte'
parent: 'presentation'
navTitle: 'Latte Templates'
title: 'Latte Templates'
description: 'Latte templating engine with routing, translations, and Vite asset integration.'
---

# Latte Templates

Latte templating engine with routing, translations, and Vite asset integration.

## Directory Structure

```
view/
├── @layout.latte              # Base layout
└── domain/
    └── controller/
        └── action.latte       # Page template
```

## Template Functions

### route() - URL Generation

Generate URLs from route names:

```latte
{* Basic route *}
<a href="{route('home')}">Home</a>

{* With parameters *}
<a href="{route('user.login', ['locale' => $translator->getShortCode()])}">
    Login
</a>

{* With multiple parameters *}
<a href="{route('user.profile', ['locale' => 'cs', 'id' => $user->getId()])}">
    Profile
</a>
```

### vite() - Asset Paths

Resolve Vite-bundled assets:

```latte
{* Image *}
<img src="{vite('assets/img/logo.svg')}" alt="Logo">

{* Favicon *}
<link rel="icon" type="image/svg+xml" href="{vite('assets/img/favicon.svg')}">

{* Entry point (CSS + JS) *}
{vite('assets/app.ts', true)|noescape}
```

The second parameter `true` indicates an entry point (includes both CSS and JS).

### {_'key'} - Translations

Translate strings using ICU MessageFormat:

```latte
{* Simple translation *}
<h1>{_'app.page.title'}</h1>

{* With parameters *}
<p>{_'user.greeting', ['name' => $userName]}</p>

{* Plurals *}
<span>{_'cart.items', ['count' => $itemCount]}</span>
```

See [Translations](/presentation/translations) for ICU MessageFormat syntax.

## Base Layout

The `view/@layout.latte` provides the HTML structure:

```latte
{varType Megio\Translation\Translator $translator}
{varType string $title}
{varType string[] $_ENV}
{varType string[] $_SERVER}

<!doctype html>
<html
    lang="{$translator->getBcp47Locale()}"
    data-posix="{$translator->getPosix()}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {block head}{/block}
    {block title}
        <title>{ifset $title}{$title} | {/ifset}{_'app.name'}</title>
    {/block}
    {block assets}
        {vite('assets/app.ts', true)|noescape}
    {/block}
</head>
<body class="{block body}{/block}">
    {block top}{/block}
    {include content}
    {block bottom}{/block}
</body>
</html>
```

### Available Blocks

| Block | Purpose |
|-------|---------|
| `head` | Meta tags, additional head content |
| `title` | Page title |
| `assets` | CSS/JS assets (override to add custom) |
| `body` | Body CSS classes |
| `top` | Content before main (toast container) |
| `content` | Main page content (required) |
| `bottom` | Content after main (scripts) |

## Creating Page Templates

### Basic Page

```latte
{varType string $description}

{extends './../../@layout.latte'}

{block head}
    {include parent}
    <meta name="description" content="{$description}">
{/block}

{block content}
    <main class="container mx-auto px-4 py-8">
        <h1>{_'page.title'}</h1>
        <p>{$description}</p>
    </main>
{/block}
```

### Page with Vue Component

```latte
{varType string $description}
{varType string $userId}

{extends './../../@layout.latte'}

{block head}
    {include parent}
    <meta name="description" content="{$description}">
{/block}

{block content}
    <div id="vue-user-profile" data-user-id="{$userId}"></div>
{/block}
```

## Variable Type Declarations

Always declare variable types at template start:

```latte
{varType string $title}
{varType string $description}
{varType App\User\Database\Entity\User $user}
{varType App\User\Database\Entity\User[] $users}
{varType Megio\Translation\Translator $translator}
{varType string[] $_ENV}
```

## Control Structures

### Conditionals

```latte
{if $user->isActive()}
    <span class="badge-active">Active</span>
{else}
    <span class="badge-inactive">Inactive</span>
{/if}

{ifset $description}
    <meta name="description" content="{$description}">
{/ifset}
```

### Loops

```latte
{foreach $products as $product}
    <div class="product">
        <h3>{$product->getName()}</h3>
        <p>{$product->getPrice()} Kč</p>
    </div>
{/foreach}

{foreach $items as $index => $item}
    <li>{$index + 1}. {$item->getName()}</li>
{/foreach}
```

### First/Last in Loop

```latte
{foreach $items as $item}
    <div class="{if $iterator->first}first{/if} {if $iterator->last}last{/if}">
        {$item->getName()}
    </div>
{/foreach}
```

## Output Escaping

```latte
{* Escaped (default, safe) *}
{$userInput}

{* Unescaped (dangerous, use carefully) *}
{$trustedHtml|noescape}

{* Escape for JavaScript *}
<script>
    const data = {$jsonData|json};
</script>
```

## Vue.js Integration

### Mount Point

Create placeholder div with unique ID:

```latte
<div id="vue-dashboard"></div>
```

### Passing Data to Vue

Use `data-*` attributes:

```latte
<div
    id="vue-user-profile"
    data-user-id="{$user->getId()}"
    data-locale="{$translator->getPosix()}"
></div>
```

Read in `assets/app.ts`:

```typescript
const el = document.getElementById('vue-user-profile');
if (el) {
    const userId = String(el.getAttribute('data-user-id'));
    const locale = String(el.getAttribute('data-locale'));

    const component = await import('@/assets/app/User/Profile.vue');
    createApp(component.default, { userId, locale }).mount(el);
}
```

## Common Patterns

### Navigation with Locale

```latte
<nav>
    <a href="{route('home.locale', ['locale' => $translator->getShortCode()])}">
        {_'nav.home'}
    </a>
    <a href="{route('user.login', ['locale' => $translator->getShortCode()])}">
        {_'nav.login'}
    </a>
</nav>
```

### Image with Vite

```latte
<img
    src="{vite('assets/img/hero.png')}"
    alt="{_'hero.alt'}"
    class="w-full h-auto"
>
```

### Conditional CSS Classes

```latte
<div class="card {if $product->isActive()}active{/if}">
    {$product->getName()}
</div>
```

## Template Rules

### Required Patterns

- Always extend `@layout.latte`
- Always declare `{varType}` for all variables
- Use `{_'key'}` for all user-facing text
- Use `{route()}` for all internal links
- Use `{vite()}` for all assets
- Use `{include parent}` in `{block head}` to keep base meta tags

### Forbidden

- Hardcoded text (use translations)
- Hardcoded URLs (use route())
- Relative asset paths (use vite())
- Business logic in templates
- Database queries in templates
- Direct PHP code (use Latte syntax)

### File Naming

- Layout: `@layout.latte`
- Page templates: `{action}.latte` in `view/{domain}/controller/`
- Mail templates: `{name}.mail.latte` in `view/{domain}/mail/`
