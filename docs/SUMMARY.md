# Table of Contents

## Introduction

* [Welcome to NeoFramework](README.md)
* [What's New](whats-new.md)
* [Upgrade Guide](upgrade-guide.md)
* [Release Notes](release-notes.md)

## Getting Started

* [Installation](getting-started/installation.md)
* [Configuration](getting-started/configuration.md)
* [Quick Start Tutorial](getting-started/quick-start.md)
* [Directory Structure](getting-started/directory-structure.md)

## Architecture Concepts

* [Request Lifecycle](architecture/lifecycle.md)
* [Service Container](architecture/container.md)
* [Service Providers](architecture/providers.md)
* [Facades](architecture/facades.md)
* [Contracts](architecture/contracts.md)

## The Basics

### Routing
* [Routing Basics](basics/routing.md)
* [Route Parameters](basics/routing-parameters.md)
* [Route Groups](basics/routing-groups.md)
* [Route Model Binding](basics/routing-model-binding.md)

### Controllers
* [Controllers](basics/controllers.md)
* [Resource Controllers](basics/controllers-resource.md)
* [Dependency Injection](basics/controllers-injection.md)

### Requests & Responses
* [Requests](basics/requests.md)
* [Form Validation](basics/validation.md)
* [Form Requests](basics/form-requests.md)
* [Responses](basics/responses.md)
* [File Uploads](basics/file-uploads.md)

### Middleware
* [Middleware](basics/middleware.md)
* [Creating Middleware](basics/middleware-custom.md)

### Views
* [Blade Templates](basics/views.md)
* [Blade Directives](basics/blade-directives.md)
* [Blade Components](basics/blade-components.md)

## Security

### Authentication
* [Authentication Overview](security/authentication.md)
* [Multi-Guard Authentication](security/multi-guard.md)
* [Password Reset](security/password-reset.md)
* [Email Verification](security/email-verification.md)
* [Remember Me](security/remember-me.md)

### Authorization
* [Authorization Overview](security/authorization.md)
* [Gates](security/gates.md)
* [Policies](security/policies.md)
* [Middleware Authorization](security/middleware-authorization.md)

### Security Best Practices
* [CSRF Protection](security/csrf.md)
* [XSS Protection](security/xss.md)
* [SQL Injection Prevention](security/sql-injection.md)
* [Encryption](security/encryption.md)

## Database

### Basics
* [Getting Started](database/getting-started.md)
* [Database Configuration](database/configuration.md)
* [Running Raw SQL](database/raw-queries.md)

### Query Builder
* [Query Builder Overview](database/query-builder.md)
* [Selects](database/query-selects.md)
* [Joins](database/query-joins.md)
* [Where Clauses](database/query-where.md)
* [Ordering & Grouping](database/query-ordering.md)
* [Aggregates](database/query-aggregates.md)

### ORM (Eloquent)
* [ORM Basics](database/orm.md)
* [Defining Models](database/orm-models.md)
* [Retrieving Models](database/orm-retrieving.md)
* [Inserting & Updating](database/orm-inserting.md)
* [Deleting Models](database/orm-deleting.md)
* [Soft Deletes](database/orm-soft-deletes.md)

### Relationships
* [Relationships Overview](database/relationships.md)
* [One to One](database/relationship-one-to-one.md)
* [One to Many](database/relationship-one-to-many.md)
* [Many to Many](database/relationship-many-to-many.md)
* [Eager Loading](database/relationship-eager-loading.md)
* [Lazy Eager Loading](database/relationship-lazy-eager.md)

### Advanced ORM
* [Accessors & Mutators](database/orm-accessors.md)
* [Attribute Casting](database/orm-casting.md)
* [Query Scopes](database/orm-scopes.md)
* [Model Events](database/orm-events.md)
* [Mass Assignment](database/orm-mass-assignment.md)

### Migrations & Seeding
* [Migrations](database/migrations.md)
* [Creating Tables](database/migrations-tables.md)
* [Modifying Tables](database/migrations-modifying.md)
* [Seeders](database/seeders.md)
* [Factories](database/factories.md)

## API Development

* [API Resources Overview](api/resources.md)
* [Resource Collections](api/resource-collections.md)
* [Conditional Attributes](api/conditional-attributes.md)
* [Resource Responses](api/resource-responses.md)
* [API Authentication](api/authentication.md)
* [Rate Limiting](api/rate-limiting.md)
* [API Versioning](api/versioning.md)

## Advanced Features

### Queue & Jobs
* [Queue Overview](advanced/queue.md)
* [Creating Jobs](advanced/queue-jobs.md)
* [Dispatching Jobs](advanced/queue-dispatching.md)
* [Job Chains](advanced/queue-chains.md)
* [Job Batches](advanced/queue-batches.md)
* [Queue Configuration](advanced/queue-configuration.md)

### Mail
* [Sending Mail](advanced/mail.md)
* [Mailable Classes](advanced/mail-mailable.md)
* [Mail Configuration](advanced/mail-configuration.md)
* [Queueing Mail](advanced/mail-queueing.md)

### Cache
* [Cache Overview](advanced/caching.md)
* [Cache Drivers](advanced/cache-drivers.md)
* [Cache Operations](advanced/cache-operations.md)
* [Cache Tags](advanced/cache-tags.md)

### Events
* [Events Overview](advanced/events.md)
* [Registering Events](advanced/events-registering.md)
* [Event Listeners](advanced/events-listeners.md)
* [Event Subscribers](advanced/events-subscribers.md)

### Logging
* [Logging Overview](advanced/logging.md)
* [Log Channels](advanced/logging-channels.md)
* [Custom Loggers](advanced/logging-custom.md)

### File Storage
* [File Storage Overview](advanced/storage.md)
* [Local Storage](advanced/storage-local.md)
* [Cloud Storage](advanced/storage-cloud.md)
* [File Operations](advanced/storage-operations.md)

## Testing

### Basics
* [Testing Overview](testing/getting-started.md)
* [PHPUnit Configuration](testing/configuration.md)
* [Writing Tests](testing/writing-tests.md)
* [Running Tests](testing/running-tests.md)

### Testing Types
* [Unit Tests](testing/unit-tests.md)
* [Feature Tests](testing/feature-tests.md)
* [HTTP Tests](testing/http-tests.md)
* [Database Tests](testing/database-tests.md)
* [Console Tests](testing/console-tests.md)

### Test Helpers
* [Assertions](testing/assertions.md)
* [HTTP Assertions](testing/http-assertions.md)
* [Database Assertions](testing/database-assertions.md)
* [Authentication Testing](testing/auth-testing.md)

### Test Data
* [Model Factories](testing/factories.md)
* [Factory States](testing/factory-states.md)
* [Factory Relationships](testing/factory-relationships.md)
* [Faker Integration](testing/faker.md)

### Mocking & Spies
* [Mocking](testing/mocking.md)
* [Spies](testing/spies.md)
* [Mock Events](testing/mock-events.md)
* [Mock Facades](testing/mock-facades.md)

## Localization

* [Localization Overview](localization/introduction.md)
* [Translation Files](localization/translation-files.md)
* [Retrieving Translations](localization/retrieving.md)
* [Pluralization](localization/pluralization.md)
* [Locale Configuration](localization/configuration.md)

## CLI Tools

### Artisan Console
* [CLI Overview](cli/introduction.md)
* [Writing Commands](cli/writing-commands.md)
* [Command I/O](cli/command-io.md)
* [Registering Commands](cli/registering.md)

### Code Generators
* [make:model](cli/generators/model.md)
* [make:controller](cli/generators/controller.md)
* [make:migration](cli/generators/migration.md)
* [make:seeder](cli/generators/seeder.md)
* [make:factory](cli/generators/factory.md)
* [make:middleware](cli/generators/middleware.md)
* [make:request](cli/generators/request.md)
* [make:resource](cli/generators/resource.md)
* [make:policy](cli/generators/policy.md)
* [make:job](cli/generators/job.md)
* [make:mail](cli/generators/mail.md)
* [make:test](cli/generators/test.md)

### Database Commands
* [migrate](cli/database/migrate.md)
* [migrate:rollback](cli/database/rollback.md)
* [migrate:fresh](cli/database/fresh.md)
* [db:seed](cli/database/seed.md)

## Developer Tools

* [Debug Toolbar](tools/debug-toolbar.md)
* [Error Pages](tools/error-pages.md)
* [Performance Profiling](tools/profiling.md)
* [Query Logging](tools/query-logging.md)

## Packages & Plugins

* [Plugin System](packages/plugin-system.md)
* [Creating Plugins](packages/creating-plugins.md)
* [Plugin Hooks](packages/plugin-hooks.md)
* [Publishing Plugins](packages/publishing.md)
* [Package Development](packages/package-development.md)

## Tutorials

* [Building a Blog](tutorials/blog.md)
* [Creating a REST API](tutorials/rest-api.md)
* [E-commerce Platform](tutorials/ecommerce.md)
* [Real-time Chat Application](tutorials/realtime-chat.md)
* [Task Management System](tutorials/task-management.md)

## API Reference

### Core
* [Application](api-reference/core/application.md)
* [Container](api-reference/core/container.md)
* [ServiceProvider](api-reference/core/service-provider.md)

### Database
* [Model](api-reference/database/model.md)
* [QueryBuilder](api-reference/database/query-builder.md)
* [Schema](api-reference/database/schema.md)
* [Migration](api-reference/database/migration.md)

### HTTP
* [Request](api-reference/http/request.md)
* [Response](api-reference/http/response.md)
* [FormRequest](api-reference/http/form-request.md)
* [JsonResource](api-reference/http/json-resource.md)
* [Router](api-reference/http/router.md)
* [Controller](api-reference/http/controller.md)

### Authentication
* [AuthManager](api-reference/auth/auth-manager.md)
* [SessionGuard](api-reference/auth/session-guard.md)
* [TokenGuard](api-reference/auth/token-guard.md)
* [Gate](api-reference/auth/gate.md)
* [Policy](api-reference/auth/policy.md)

### Queue
* [Job](api-reference/queue/job.md)
* [Queue](api-reference/queue/queue.md)
* [Bus](api-reference/queue/bus.md)

### Mail
* [Mailable](api-reference/mail/mailable.md)
* [Mailer](api-reference/mail/mailer.md)

### Cache
* [Cache](api-reference/cache/cache.md)
* [CacheManager](api-reference/cache/cache-manager.md)

### Validation
* [Validator](api-reference/validation/validator.md)
* [ValidationRule](api-reference/validation/validation-rule.md)

### Testing
* [TestCase](api-reference/testing/test-case.md)
* [Factory](api-reference/testing/factory.md)

## Appendix

* [Helper Functions](appendix/helpers.md)
* [Configuration Options](appendix/configuration.md)
* [Error Codes](appendix/error-codes.md)
* [Glossary](appendix/glossary.md)
* [FAQ](appendix/faq.md)

## Contributing

* [Contribution Guide](contributing/guide.md)
* [Code Style](contributing/code-style.md)
* [Pull Request Guidelines](contributing/pull-requests.md)
* [Bug Reports](contributing/bug-reports.md)

## Resources

* [Community](resources/community.md)
* [Learning Resources](resources/learning.md)
* [Packages](resources/packages.md)
* [Tools](resources/tools.md)
* [Videos & Tutorials](resources/videos.md)
