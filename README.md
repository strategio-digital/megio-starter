---
layout: 'page'
uri: '/'
position: 1
slug: 'home'
navTitle: 'Getting Started'
title: 'Getting Started'
description: 'Installation and setup instructions for Megio.'
---

# Getting Started

- Docs: https://megio.dev

## Installation Guide

1. **Build the project**
   ```bash
   cp .env.example .env && make build
   ```

2. **Start the development server**
   ```bash
   make serve
   ```

3. **Create admin user**
   ```bash
   docker compose exec app bin/console admin admin@test.cz Test1234
   ```
   
4. **Run tests**
   ```bash
   make test
   ```
   
5. **Access the application**
   - App: [http://localhost:8090](http://localhost:8090)
   - Admin: [http://localhost:8090/app/admin](http://localhost:8090/app/admin)