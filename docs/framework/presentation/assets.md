---
layout: 'page'
uri: '/presentation/assets'
position: 2
slug: 'presentation-assets'
parent: 'presentation'
navTitle: 'Frontend Assets'
title: 'Frontend Assets'
description: 'Vite bundler with Vue.js components, TypeScript, and Tailwind CSS integration.'
---

# Frontend Assets

Vite bundler with Vue.js components, TypeScript, and Tailwind CSS integration.

## How It Works

1. **Vite** bundles TypeScript, Vue, and CSS
2. **Entry points** (`app.ts`, `panel.ts`) define bundles
3. **Vue components** mount to placeholder divs in Latte
4. **Tailwind CSS** provides utility classes
5. **HMR** enables live reload during development

## Directory Structure

```
assets/
├── app.ts                 # Main entry point
├── panel.ts               # Admin panel entry
├── vite.env.d.ts          # TypeScript definitions
├── app/                   # Vue components by domain
│   ├── User/
│   │   └── LoginForm/
│   │       └── LoginForm.vue
│   └── Dashboard/
│       └── Dashboard.vue
├── app-ui/                # Shared UI components
│   ├── Toast/
│   ├── Button/
│   └── Translations/
├── css/
│   └── tailwind.css       # Tailwind entry
├── img/                   # Static images
│   ├── favicon.svg
│   └── logo.svg
└── ts/                    # Shared TypeScript
    └── Plugins/
        └── MegioApi.ts
```

## Vite Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    resolve: {
        alias: {
            '@/assets': '/assets',
        }
    },
    plugins: [
        vue(),
        tailwindcss(),
        laravel({
            publicDirectory: 'www',
            buildDirectory: 'temp',
            hotFile: 'temp/vite.hot',
            input: ['assets/app.ts', 'assets/panel.ts'],
            refresh: ['assets/**', 'view/**']
        })
    ]
})
```

## Development

### Start Dev Server

```bash
# Start Vite dev server with HMR
yarn dev

# Or via Make
make dev
```

### Build for Production

```bash
# Build optimized assets
yarn build

# Or via Make
make build
```

## Entry Point (app.ts)

```typescript
// assets/app.ts

// Import static files
import '@/assets/img/favicon.svg';

// Import styles
import '@/assets/css/tailwind.css';

// Initialize plugins
import MegioApi from '@/assets/ts/Plugins/MegioApi.ts';
MegioApi();

// Load translations
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';
const { load } = useTranslation();
load();

import { megio } from 'megio-api';
import { createApp } from 'vue';

// Mount Vue components
const loginEl = document.getElementById('vue-user-login-form');
if (loginEl) {
    const LoginForm = await import('@/assets/app/User/LoginForm/LoginForm.vue');
    createApp(LoginForm.default).mount(loginEl);
}

// Conditional mounting for authenticated users
if (megio.auth.user.hasRole('user')) {
    const dashboardEl = document.getElementById('vue-dashboard');
    if (dashboardEl) {
        const Dashboard = await import('@/assets/app/Dashboard/Dashboard.vue');
        createApp(Dashboard.default).mount(dashboardEl);
    }
}
```

## Creating Vue Component

### 1. Create Component

```vue
<!-- assets/app/Product/ProductForm/ProductForm.vue -->
<script setup lang="ts">
import { reactive, ref } from 'vue';
import { megio } from 'megio-api';
import Button from '@/assets/app-ui/Button/Button.vue';

type FormData = {
    name: string;
    price: string;
};

type FormErrors = {
    name?: string;
    price?: string;
};

const form = reactive<FormData>({
    name: '',
    price: '',
});

const errors = ref<FormErrors>({});
const loading = ref(false);

async function handleSubmit() {
    loading.value = true;
    errors.value = {};

    const response = await megio.fetch('/api/v1/products', {
        method: 'POST',
        body: JSON.stringify(form),
    });

    loading.value = false;

    if (response.ok === false) {
        const data = await response.json();
        // Handle validation errors from backend
        if (data.errors) {
            errors.value = data.errors;
        }
        return;
    }

    window.toast.success('Product created');
}
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <div>
            <label for="name">Name</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                :class="{ 'border-red-500': errors.name }"
            />
            <span v-if="errors.name" class="text-red-500">{{ errors.name }}</span>
        </div>

        <div>
            <label for="price">Price</label>
            <input
                id="price"
                v-model="form.price"
                type="text"
                :class="{ 'border-red-500': errors.price }"
            />
            <span v-if="errors.price" class="text-red-500">{{ errors.price }}</span>
        </div>

        <Button type="submit" :loading="loading">
            Create Product
        </Button>
    </form>
</template>
```

### 2. Register in app.ts

```typescript
// assets/app.ts

const productFormEl = document.getElementById('vue-product-form');
if (productFormEl) {
    const ProductForm = await import('@/assets/app/Product/ProductForm/ProductForm.vue');
    createApp(ProductForm.default).mount(productFormEl);
}
```

### 3. Create Latte Template

```latte
{* view/product/controller/create.latte *}
{extends './../../@layout.latte'}

{block content}
    <div id="vue-product-form"></div>
{/block}
```

## Passing Data to Vue

### Via data-* Attributes

```latte
{* Latte template *}
<div
    id="vue-product-edit"
    data-product-id="{$productId}"
    data-initial-name="{$product->getName()}"
></div>
```

```typescript
// app.ts
const el = document.getElementById('vue-product-edit');
if (el) {
    const productId = String(el.getAttribute('data-product-id'));
    const initialName = String(el.getAttribute('data-initial-name'));

    const ProductEdit = await import('@/assets/app/Product/ProductEdit.vue');
    createApp(ProductEdit.default, { productId, initialName }).mount(el);
}
```

```vue
<!-- ProductEdit.vue -->
<script setup lang="ts">
const { productId, initialName = '' } = defineProps<{
    productId: string;
    initialName?: string;
}>();
</script>
```

## Import Aliases

Always use `@/` alias for imports:

```typescript
// CORRECT
import Button from '@/assets/app-ui/Button/Button.vue';
import { useTranslation } from '@/assets/app-ui/Translations/useTranslation';

// WRONG - never use relative paths
import Button from '../../../app-ui/Button/Button.vue';
```

## Toast System

```typescript
// Show toast notifications
window.toast.success('Operation completed');
window.toast.error('Something went wrong');
window.toast.info('Please wait...');
window.toast.warning('Are you sure?');
```

## Megio API Client

```typescript
import { megio } from 'megio-api';

// Fetch with authentication
const response = await megio.fetch('/api/v1/products');

// Check user roles
if (megio.auth.user.hasRole('admin')) {
    // Admin-only code
}

// Get current user
const user = megio.auth.user.get();
```

## Assets Rules

### Required Patterns

- Use `@/` alias for all imports
- Use `<script setup lang="ts">` in Vue components
- Use `type` (not `interface`) for form types
- Use `reactive<Type>()` and `ref<Type>()` with explicit typing
- Mount components conditionally with `if (el)`
- Handle all validation on backend (no frontend validation)

### Forbidden

- Relative imports (`../..`)
- Frontend-only validation
- Inline styles (use Tailwind)
- Direct DOM manipulation in Vue

### Component Naming

```
assets/app/{Domain}/{ComponentName}/{ComponentName}.vue

# Examples:
assets/app/User/LoginForm/LoginForm.vue
assets/app/Product/ProductList/ProductList.vue
assets/app/Dashboard/Dashboard.vue
```
