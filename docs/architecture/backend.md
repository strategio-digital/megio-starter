---
layout: 'page'
uri: '/architecture/backend'
position: 2
slug: 'architecture-backend'
parent: 'architecture'
navTitle: 'Backend Architecture'
title: 'Backend Architecture Guide'
description: 'Layer hierarchy, dependency matrix, and rules for backend architecture.'
---

# Backend Architecture Guide

This document defines the layered architecture for application. It describes which layers exist, what responsibilities
they have, and which dependencies are allowed between them.

**Follow SRP (Single Responsibility Principle)**: Each class has exactly one responsibility. Controller handles HTTP,
Facade handles business logic, Repository handles data access. This ensures maintainable, testable codebase.

## Layer Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│ Entry: Controller, Command, Request, Worker, Subscriber,    │
│        Processor (can use Facades from different domains)   │
└─────────────┬──────────────────────────────┬────────────────┘
              │                              │
              ▼                              ▼
┌──────────────────────────────┐  ┌──────────────────────────┐
│           Facade             │  │       Integration        │
│  (domain business logic,     │  │  (external system sync)  │
│   orchestrates services)     │  │                          │
└──────────────┬───────────────┘  └────────────┬─────────────┘
               │                               │
               ▼                               ▼
┌─────────────────────────────────────────────────────────────┐
│  Service: Resolver, Validator, Helper, Mailer, Factory,     │
│           Assembler (utility classes used by Facade)        │
└─────────────────────────────────────────────────────────────┘
               │                               │
               ▼                               ▼
┌──────────────────────────────┐  ┌──────────────────────────┐
│          Repository          │  │    Client → Endpoint     │
└──────────────┬───────────────┘  └──────────────────────────┘
               ▼
┌──────────────────────────────┐
│           Entity             │
└──────────────────────────────┘
```

## Layer Rules

Each layer has a single responsibility (SRP). Never mix responsibilities across layers.

| Layer       | Responsibility (SRP)                                             |
|-------------|------------------------------------------------------------------|
| Facade      | Single operation per facade, one `execute()` method, orchestrates services |
| Integration | External system sync, uses Client, tracks IDs via ImportIdFacade |
| Repository  | Database queries, CRUD                                           |
| Resolver    | Business rules/decisions, stateless (service class)              |
| Validator   | Validates business rules, throws exceptions (service class)      |
| Mailer      | Email composition and sending (service class)                    |
| Factory     | Entity creation from DTOs, pure, no DB (service class)           |
| Helper      | Pure functions, data transformation (service class)              |
| Assembler   | Builds complex DTOs, may query DB, not pure                      |
| Client      | HTTP client for one external system, returns Endpoints           |
| Endpoint    | Single API URI, methods for HTTP verbs, returns DTOs             |
| Controller  | Renders Latte templates, passes data to view (entry point)       |
| Command     | CLI operations (entry point)                                     |
| Request     | REST API handler (entry point)                                   |
| Worker      | Background job processing from Queue (entry point)               |
| Subscriber  | Event handling - Kernel, Doctrine events (entry point)           |
| Processor   | WebHook/event processing (entry point)                           |
| Recipe      | CRUD & admin-panel configuration                                 |

**Key principles:**
- **SRP**: Each class has exactly one responsibility
- Facade is the primary business logic layer, orchestrates service classes
- Entry (Controller, Command, Request, Worker, Subscriber) can use multiple Facades from different domains
- Facade cannot call another Facade (Entry handles cross-domain coordination)
- Service classes (Resolver, Validator, Helper, Mailer, Factory) are utility classes used by Facade

## Dependency Matrix

Reading: row **can use** column (e.g. Facade ✓ Rep = Facade can call Repository)

| From ↓ \ To → | Fcd | Int | Rep | Fac | Hlp | Asm | Res | Val | Cli | Endp | Ent |
|---------------|-----|-----|-----|-----|-----|-----|-----|-----|-----|------|-----|
| Entry         | ✓   | ✓   |     |     |     |     |     |     |     |      |     |
| Facade        |     |     | ✓   | ✓   | ✓   | ✓   | ✓   | ✓   |     |      |     |
| Integration   |     |     |     | ✓   | ✓   |     |     |     | ✓   |      |     |
| Assembler     |     |     | ✓   | ✓   | ✓   |     |     |     |     |      |     |
| Resolver      |     |     |     |     | ✓   |     |     |     |     |      |     |
| Validator     |     |     |     |     | ✓   |     |     |     |     |      |     |
| Client        |     |     |     |     |     |     |     |     |     | ✓    |     |
| Repository    |     |     |     |     |     |     |     |     |     |      | ✓   |
| Factory       |     |     |     |     | ✓   |     |     |     |     |      | ✓   |

Fcd=Facade, Int=Integration, Rep=Repository, Fac=Factory, Hlp=Helper, Asm=Assembler, Res=Resolver, Val=Validator,
Cli=Client, Endp=Endpoint, Ent=Entity

**Service classes** (Resolver, Validator, Helper, Mailer, Factory, Assembler) are utility classes orchestrated by Facade.

**Forbidden:**
- Facade → Facade (use Entry for cross-domain coordination)
- Integration → Repository (data flows through Facade)
- Entry → EntityManager (always use Facade)
- Lower → Higher layer

## Why Integration Cannot Use Repository

Integration fetches data from external API but **must not save directly to database**. Data flows up to Entry, which
delegates persistence to Facade.

```
❌ Wrong: Integration saves directly
Command → Integration → Client (fetch) → Repository (save)

✅ Correct: Entry orchestrates cross-domain
Command
    ├─ Integration → Client (fetch, returns DTO)
    └─ Facade → Repository (save)
```

**Reason**: Facade owns business logic for entity (validation, transactions, EntityLogger). Bypassing it creates
inconsistent data.

## Layer Categories

### Entry Points (application entry)

| Layer      | When to use                                 |
|------------|---------------------------------------------|
| Controller | Renders Latte templates (web pages)         |
| Command    | CLI operations                              |
| Request    | REST API handler                            |
| Worker     | Background job processing (Queue)           |
| Subscriber | Event handling (Kernel, Doctrine)           |
| Processor  | WebHook/event processing                    |

### Business Logic (where logic lives)

| Layer       | When to use                                                        |
|-------------|-------------------------------------------------------------------|
| Facade      | Single operation per facade with `execute()` method (primary layer) |
| Integration | Sync with external system                                          |

**Facade naming:** `{Action}{Entity}Facade::execute()` (e.g., `RegisterUserFacade::execute()`)

### Service Classes (utility layer, orchestrated by Facade)

| Layer     | When to use                                |
|-----------|--------------------------------------------|
| Resolver  | Business decisions (true/false, selection) |
| Validator | Business rule validation                   |
| Mailer    | Email composition and sending              |

### Data Access (working with data)

| Layer      | When to use                |
|------------|----------------------------|
| Repository | DB queries, CRUD           |
| Entity     | DB entity (Doctrine)       |
| Filter     | Dynamic query filtering    |
| Applier    | Applies filters to queries |

### External Communication (outbound communication)

| Layer         | When to use                                   |
|---------------|-----------------------------------------------|
| Client        | HTTP client for external API                  |
| Endpoint      | Single API URI (may have get/post/put/delete) |
| Configuration | API credentials, URLs                         |

### Data Transformation (data transformation)

| Layer      | When to use                                   |
|------------|-----------------------------------------------|
| Assembler  | Builds DTO from multiple sources (may use DB) |
| Factory    | Creates entity from DTO (pure, no DB)         |
| Helper     | Pure functions, transformations               |
| Normalizer | Data normalization/denormalization            |

### Data Objects (data structures)

| Layer       | When to use                          |
|-------------|--------------------------------------|
| Dto         | Data transfer object                 |
| RequestData | Input DTO (body, query params)       |
| ResponseDto | Output DTO                           |
| Collection  | Typed collection of DTOs or entities |
| Enum        | Enumeration types                    |
| Exception   | Custom exceptions                    |

### Admin/UI Configuration

| Layer  | When to use                          |
|--------|--------------------------------------|
| Recipe | CRUD & admin-panel configuration     |

