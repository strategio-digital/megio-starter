# Project name
Skeleton for your apps, webs & APIs built on Megio.

- Docs: https://megio.dev

## Installation Guide

### Setup Steps

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