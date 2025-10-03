# Project Architecture

## Overview

This project uses the **Megio Framework** - a PHP framework with domain-driven design.

## Domain Structure

```
app/DomainName/
├── Console/       # CLI commands
├── Database/      # Entities, Repositories, Fields, Interfaces
├── Dto/           # Data Transfer Objects with validation
├── Facade/        # Business logic layer
├── Http/          # Controllers, Requests & API Clients
├── Recipe/        # CRUD configurations
├── Subscriber/    # Event subscribers
└── Worker/        # Background jobs
```

## Key Patterns

- Domain separation (`app/User/`, `app/Admin/`, etc.)
- Dependency injection via constructor
- Type safety with strict PHP types and Symfony validator with PHP attributes
- Always use strict comparison (`===`, `!==`) - never use negations like `!`
- Never use `empty()` function - use explicit checks instead
- Never use `isset()` function - use explicit null checks instead
- ID is always string - UUIDv6, never int
- Latte templates for views
- Vue.js components for interactivity
- RequestSerializer with DTO classes instead of Nette Schema and `assert()`

## SOLID Principles

- **SRP**: Each class has single responsibility (Controller=HTTP, Facade=logic, Repository=data, DTO=validation)
- **OCP**: Extend via new classes, not modifying existing ones
- **LSP**: Derived classes must be substitutable for base classes
- **ISP**: Create specific interfaces, not general-purpose ones
- **DIP**: Depend on abstractions, use constructor injection

## Testing

- Uses **PHPUnit** testing framework with `tests/Feature/` and `tests/Unit/` structure
- Always run `make test` after PHP or Vue code modifications to ensure tests pass
- **Test Structure Rules:**
    - Use standard PHPUnit syntax: `public function testMethodName(): void {}`
    - **Feature tests:** Extend `Tests\TestCase` for DI container access, use `$this->getService(ClassName::class)`
    - **Unit tests:** Extend `PHPUnit\Framework\TestCase`, do NOT use `$this->getService()` - create objects manually with `new ClassName()` or use mocks
    - Use PHPUnit assertions: `$this->assertSame()`, `$this->assertInstanceOf()`, `$this->assertCount()`
    - Add proper namespaces following PSR-4: `namespace Tests\Unit\Domain\Service;`
    - Create real API tests with external services when possible (use `$_ENV['TEST_API_KEY']` pattern)
    - Feature tests should test integration with real external APIs to verify actual behavior

## Formatting

- Always run `make format` after code modifications to ensure consistent formatting
- Uses PHP CS Fixer with PER-CS3.0 standard and Biome for TypeScript/Vue

## Adding New Functionality - Complete Workflow

When adding new functionality (e.g., user registration), follow this complete checklist:

### 1. Backend PHP Components

#### Request Handler (for API endpoints)

- Create Request class in `app/Domain/Http/Request/` extending `Megio\Http\Request\Request`
- Create corresponding DTO class in `app/Domain/Dto/` with Symfony validation attributes
- Inject `RequestSerializer` via constructor for deserialization and validation
- Implement `schema()` returning empty array `[]`
- Implement `process()` with proper error handling using try/catch
- Use `$this->requestSerializer->deserialize(DtoClass::class, $data)` for validation
- Return `$this->json()` for success, `$this->error()` for errors

#### Request Validation & Mapping

- All requests use RequestSerializer for validation and deserialization
- DTO classes implement `RequestDtoInterface` with Symfony validation attributes
- Use explicit type declarations with `#[Assert\Type()]` constraints
- DateTime fields use `#[Assert\DateTime(format: 'Y-m-d\TH:i')]` for ISO format
- Nullable fields explicitly typed as `?string` with `#[Assert\Type(['null', 'string'])]`
- Range validation with `#[Assert\GreaterThan()]` and `#[Assert\LessThanOrEqual()]`

#### HTTP Clients & External APIs

- Create Client classes in `app/Domain/Http/Client/` as readonly final classes
- Use Guzzle HTTP client via `ClientFactory` with proper base URI and timeout configuration
- Structure endpoints as separate classes in `Client/Endpoint/` subdirectory
- All client methods return endpoint instances for clean API separation
- Handle authentication via headers (API keys, tokens) in client configuration

#### Controller Method

- Add method to appropriate controller in `app/Domain/Http/Controller/`
- Return Latte template render with title and description

#### Latte Template

- Create `.latte` file in `view/domain/controller/`
- Include varType declarations, extend layout, add Vue mount point div with unique ID

#### DTO Classes

- Create DTO classes in `app/Domain/Dto/` for request validation
- Use readonly classes with constructor property promotion
- Add Symfony validation attributes (`#[Assert\NotBlank]`, `#[Assert\Email]`, etc.)
- Set default values in constructor parameters where appropriate

#### Facade (Business Logic)

- Create Facade in `app/Domain/Facade/` as readonly class
- Inject EntityManager via constructor
- Implement business logic methods accepting DTO objects instead of individual parameters
- Add proper exception declarations (@throws)
- Find methods belong to repository, not facade
- Update methods should only take entity parameter, then persist and flush

#### Integration Layer

- Create Integration classes in `app/Domain/Integration/` as orchestrators
- Extract business logic to Facade classes in `app/Domain/Facade/`
- Integration classes delegate to facades and handle external API operations
- Register both Integration and Facade classes in `config/app.neon`

#### Repository Rules

- Never return array from repository, use ArrayCollection when returning entities
- Always use `use` statements, never long namespaces in code

#### Dependency Injection

- Register new services in `config/app.neon` under services section

### 2. Routing Configuration

#### Web Routes (for pages)

- Add route in `router/web.php` with controller method reference
- Set appropriate methods (['GET']) and auth options

#### REST Routes (for API)

- Add route in `router/rest.php` with Request class reference
- Set appropriate methods (['POST']) and auth: false for public endpoints

### 3. Frontend Vue Components

#### Vue Component

- Create component in `assets/app/ComponentName/ComponentName.vue`
- Use `<script setup lang="ts">` with proper TypeScript types
- Define form types and error types using `type` (not interface)
- Use `reactive<Type>()` and `ref<Type>()` with explicit typing
- For props use destructuring: `const { prop1, prop2 = 'default' } = defineProps<Type>()`
- No frontend validation - all validation handled by backend RequestSerializer
- Handle API calls with `megio.fetch()` and proper error handling from backend
- Use existing UI components from `@/assets/app-ui/`

#### Registration in app.ts

- Import component in `assets/app.ts`
- Add conditional mounting with DOM element existence check:

```typescript
const el = document.getElementById('vue-<domain>-<Project-edit-form>');
if (el) {
    const component = await import('@/assets/app/Domain/Component.vue');
    const someId = String(projectEditEl.getAttribute('data-some-id'));
    createApp(component.default, {someId}).mount(el);
}
```