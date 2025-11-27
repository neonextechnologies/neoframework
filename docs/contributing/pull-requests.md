# 🔄 Pull Request Guidelines

This guide explains how to submit pull requests (PRs) to NeoFramework, ensuring a smooth review process and successful contribution.

## Table of Contents

- [Before You Start](#before-you-start)
- [Branch Naming](#branch-naming)
- [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)
- [PR Description](#pr-description)
- [Testing Requirements](#testing-requirements)
- [Code Review Process](#code-review-process)
- [Common Issues](#common-issues)

## 🎯 Before You Start

### Check Existing Work

1. **Search existing PRs** to avoid duplicate work
2. **Check open issues** for related discussions
3. **Review the roadmap** to ensure alignment
4. **Discuss major changes** in an issue first

### Prepare Your Environment

```bash
# Update your fork
git fetch upstream
git checkout main
git merge upstream/main

# Create a new branch
git checkout -b feature/your-feature-name

# Install dependencies
composer install

# Run tests to ensure baseline
composer test
```

### Communication

For significant changes:
- **Open an issue** first to discuss the approach
- **Comment on existing issues** if you plan to work on them
- **Ask questions** in GitHub Discussions or Discord
- **Coordinate** with maintainers to avoid conflicts

## 🌿 Branch Naming

Use clear, descriptive branch names following this convention:

### Format

```
<type>/<description>
```

### Types

- `feature/` - New features or enhancements
- `bugfix/` - Bug fixes
- `hotfix/` - Critical fixes for production issues
- `refactor/` - Code refactoring without behavior changes
- `docs/` - Documentation changes
- `test/` - Adding or updating tests
- `chore/` - Maintenance tasks, dependency updates

### Examples

```bash
# ✅ Good
feature/user-authentication
feature/add-caching-layer
bugfix/fix-route-parameter-parsing
bugfix/resolve-memory-leak-in-query-builder
hotfix/critical-security-vulnerability
refactor/improve-container-performance
docs/update-routing-documentation
test/add-middleware-tests
chore/update-dependencies

# ❌ Bad
fix
my-feature
update
patch-1
branch-2024
john-working-branch
```

### Multiple Word Descriptions

Use hyphens to separate words:

```bash
# ✅ Good
feature/email-notification-system
bugfix/fix-validation-error-messages
docs/add-installation-guide

# ❌ Bad
feature/email_notification_system
bugfix/fixValidationErrorMessages
docs/AddInstallationGuide
```

### Issue References

Include issue numbers when working on specific issues:

```bash
feature/issue-123-add-rate-limiting
bugfix/issue-456-fix-session-handling
```

## 📝 Commit Messages

Good commit messages make it easier to understand changes and maintain project history.

### Format

Follow the Conventional Commits specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Components

**Type** (required):
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code style changes (formatting, missing semicolons)
- `refactor` - Code refactoring
- `test` - Adding or updating tests
- `chore` - Maintenance tasks
- `perf` - Performance improvements

**Scope** (optional):
- Component or module affected: `router`, `container`, `validation`, etc.

**Subject** (required):
- Brief description (50 characters or less)
- Use imperative mood: "add" not "added"
- Don't capitalize first letter
- No period at the end

**Body** (optional):
- Detailed explanation of changes
- Wrap at 72 characters
- Explain what and why, not how

**Footer** (optional):
- Breaking changes: `BREAKING CHANGE: description`
- Issue references: `Fixes #123`, `Closes #456`

### Examples

**Simple feature**:

```
feat(router): add support for route parameters

Add ability to define route parameters using curly braces.
Parameters are automatically injected into controller methods.

Closes #123
```

**Bug fix**:

```
fix(validation): resolve issue with nested array validation

The validator was not properly handling nested arrays when
validating complex data structures. This fix ensures that
all nested levels are validated correctly.

Fixes #456
```

**Breaking change**:

```
feat(container)!: change singleton binding API

BREAKING CHANGE: The `bind()` method no longer accepts a third
parameter for singleton binding. Use `singleton()` method instead.

Before:
$container->bind(Service::class, ServiceImpl::class, true);

After:
$container->singleton(Service::class, ServiceImpl::class);

Closes #789
```

**Documentation**:

```
docs: update middleware documentation with examples

Add comprehensive examples for creating custom middleware.
Include common use cases and best practices.
```

**Multiple changes**:

```
feat: add request validation and error handling

- Implement ValidationMiddleware for automatic validation
- Add FormRequest base class for request validation
- Create ValidationException for validation errors
- Update error handler to format validation errors

Closes #234, #235
```

### Best Practices

**Do**:
```bash
# ✅ Good - Clear and specific
feat(cache): add Redis driver support
fix(router): resolve 404 error for nested routes
docs: add examples to validation guide
test: add unit tests for Container class
refactor(database): extract query builder logic

# ✅ Good - With detailed body
feat(auth): implement JWT authentication

Add JWT-based authentication system with:
- Token generation and validation
- Refresh token support
- Automatic token renewal

Closes #123
```

**Don't**:
```bash
# ❌ Bad - Too vague
fix: fixed bug
feat: updates
docs: changes

# ❌ Bad - Too long subject
feat(router): add support for route parameters with multiple types and automatic type casting

# ❌ Bad - Past tense
feat: added caching
fix: fixed validation bug

# ❌ Bad - Not descriptive
update files
quick fix
wip
```

### Atomic Commits

Make commits atomic and focused:

```bash
# ✅ Good - Separate commits for separate concerns
git commit -m "feat(cache): add cache interface"
git commit -m "feat(cache): implement file cache driver"
git commit -m "feat(cache): implement redis cache driver"
git commit -m "test: add cache driver tests"
git commit -m "docs: add cache documentation"

# ❌ Bad - Everything in one commit
git commit -m "add caching feature"
```

## 🚀 Pull Request Process

### Step 1: Prepare Your Changes

```bash
# Ensure your branch is up to date
git checkout main
git pull upstream main
git checkout your-branch
git rebase main

# Run tests
composer test

# Run code style checks
composer cs:fix

# Run static analysis
composer analyse
```

### Step 2: Push Your Branch

```bash
git push origin your-branch
```

### Step 3: Create Pull Request

1. Go to your fork on GitHub
2. Click "Compare & pull request"
3. Select the base repository and branch
4. Fill out the PR template
5. Submit the pull request

### Step 4: Address Feedback

```bash
# Make requested changes
git add .
git commit -m "refactor: address review comments"
git push origin your-branch

# Or amend the last commit if appropriate
git add .
git commit --amend
git push --force origin your-branch
```

### Step 5: Keep PR Updated

```bash
# Sync with upstream regularly
git fetch upstream
git rebase upstream/main
git push --force origin your-branch
```

## 📋 PR Description

Use this template for your PR description:

```markdown
## Description

Brief description of what this PR does.

## Motivation

Why is this change needed? What problem does it solve?

## Changes

- Change 1
- Change 2
- Change 3

## Type of Change

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Code refactoring
- [ ] Performance improvement

## Testing

Describe the tests you ran and how to reproduce them:

```php
// Example test code
$user = User::create(['name' => 'Test']);
$this->assertTrue($user->exists);
```

## Checklist

- [ ] My code follows the code style of this project
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] Any dependent changes have been merged and published

## Screenshots (if applicable)

Add screenshots to help explain your changes.

## Related Issues

Closes #123
Related to #456

## Breaking Changes

List any breaking changes and migration steps required.

## Additional Notes

Any additional information reviewers should know.
```

### Example PRs

**Feature PR**:

```markdown
## Description

Add Redis cache driver support to the caching system.

## Motivation

Redis is a popular caching solution for high-performance applications.
Many users have requested Redis support as an alternative to file-based caching.

## Changes

- Created `RedisCache` class implementing `CacheInterface`
- Added Redis configuration options
- Implemented all cache operations (get, set, delete, flush)
- Added support for tags and TTL
- Added comprehensive tests

## Type of Change

- [x] New feature (non-breaking change which adds functionality)

## Testing

```php
$cache = new RedisCache($config);
$cache->set('key', 'value', 3600);
$this->assertEquals('value', $cache->get('key'));
```

All existing cache tests pass with the new driver.

## Checklist

- [x] Code follows project style
- [x] Self-review completed
- [x] Code commented
- [x] Documentation updated
- [x] No new warnings
- [x] Tests added
- [x] Tests pass

## Related Issues

Closes #234
```

**Bug Fix PR**:

```markdown
## Description

Fix route parameter parsing for optional parameters.

## Motivation

Optional route parameters were not being parsed correctly when
the parameter was at the end of the route pattern.

## Changes

- Updated route parser regex to handle optional parameters
- Added validation for parameter format
- Fixed edge cases with trailing slashes

## Type of Change

- [x] Bug fix (non-breaking change which fixes an issue)

## Testing

Before:
```php
Route::get('/users/{id?}'); // Would fail with 404
```

After:
```php
Route::get('/users/{id?}'); // Works correctly
// /users -> matches
// /users/123 -> matches with id=123
```

## Checklist

- [x] All items checked

## Related Issues

Fixes #456
```

## 🧪 Testing Requirements

### Test Coverage

All PRs must include tests:

- **Bug fixes**: Add test that fails before fix, passes after
- **New features**: Test all new functionality
- **Refactoring**: Ensure existing tests still pass

### Test Types

```php
// Unit tests
class ValidationTest extends TestCase
{
    public function test_required_rule_validates_correctly(): void
    {
        $validator = new Validator(['name' => ''], ['name' => 'required']);
        $this->assertFalse($validator->passes());
    }
}

// Feature tests
class UserControllerTest extends TestCase
{
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
}
```

### Running Tests

```bash
# All tests
composer test

# Specific test file
./vendor/bin/phpunit tests/Unit/ValidationTest.php

# Specific test method
./vendor/bin/phpunit --filter test_required_rule_validates_correctly

# With coverage
composer test:coverage
```

### Test Standards

- **One assertion per test** when possible
- **Descriptive test names**
- **Arrange-Act-Assert pattern**
- **Mock external dependencies**
- **Test edge cases**

## 👀 Code Review Process

### Review Timeline

- **Initial review**: Within 48 hours
- **Follow-up reviews**: Within 24 hours
- **Final approval**: When all feedback addressed

### Review Criteria

Reviewers check for:

1. **Functionality**: Does it work as intended?
2. **Code Quality**: Is it clean and maintainable?
3. **Tests**: Are there adequate tests?
4. **Documentation**: Are changes documented?
5. **Performance**: Any performance implications?
6. **Security**: Any security concerns?
7. **Breaking Changes**: Impact on existing code?

### Reviewer Feedback

**Types of feedback**:

- 🟢 **Approval**: Ready to merge
- 🟡 **Request Changes**: Issues that must be addressed
- 🔵 **Comment**: Suggestions or questions

**Example feedback**:

```markdown
# Request Changes
This looks good overall, but a few things need to be addressed:

1. **Line 45**: This method should validate input before processing
   ```php
   // Suggestion:
   if (!$this->isValid($input)) {
       throw new ValidationException('Invalid input');
   }
   ```

2. **Line 78**: Consider extracting this to a separate method for clarity

3. **Tests**: Please add a test case for the error scenario

# Comment
Nice work on the documentation! One suggestion:

Consider adding an example for the most common use case at the top
of the documentation section.
```

### Responding to Feedback

**Good responses**:

```markdown
# ✅ Good - Acknowledge and address
Thanks for the feedback!

1. Added input validation as suggested
2. Extracted method to `processData()` for better clarity
3. Added test case for error scenario in commit abc123

Let me know if there's anything else to address!
```

```markdown
# ✅ Good - Ask for clarification
Thanks for reviewing!

Regarding point 2 about extracting the method - could you clarify
which part you'd like extracted? The entire validation logic or
just the complex condition check?
```

**Poor responses**:

```markdown
# ❌ Bad - Defensive or dismissive
I don't think this is necessary. The code works fine as is.

# ❌ Bad - No response
[Makes changes without acknowledging feedback]

# ❌ Bad - Arguing without substance
I disagree with all these suggestions.
```

### Multiple Reviewers

If multiple reviewers provide conflicting feedback:

1. **Ask for clarification** from maintainers
2. **Explain your reasoning** for your approach
3. **Be open to discussion**
4. **Let maintainers make final decision**

## ⚠️ Common Issues

### Merge Conflicts

```bash
# Update your branch with main
git fetch upstream
git rebase upstream/main

# Resolve conflicts
git add .
git rebase --continue

# Force push (PR branch only!)
git push --force origin your-branch
```

### Failed Tests

```bash
# Run tests locally
composer test

# Check specific failure
./vendor/bin/phpunit tests/Unit/FailingTest.php --verbose

# Fix and commit
git add .
git commit -m "test: fix failing test"
git push origin your-branch
```

### Code Style Issues

```bash
# Auto-fix style issues
composer cs:fix

# Check remaining issues
composer cs:check

# Commit fixes
git add .
git commit -m "style: apply code style fixes"
git push origin your-branch
```

### Large PRs

If your PR is too large:

1. **Split into smaller PRs** if possible
2. **Explain the scope** in the description
3. **Provide context** for reviewers
4. **Consider creating an RFC** for major changes

### Stale PRs

If your PR becomes stale:

```bash
# Rebase on latest main
git fetch upstream
git rebase upstream/main

# Resolve conflicts
# Push updates
git push --force origin your-branch

# Comment on PR
```

Then add a comment:
```markdown
Rebased on latest main and resolved conflicts. Ready for review!
```

## ✅ Final Checklist

Before requesting review:

- [ ] Branch is up to date with main
- [ ] All tests pass locally
- [ ] Code style checks pass
- [ ] Documentation updated
- [ ] PR description is complete
- [ ] Commits are clean and well-described
- [ ] Self-review completed
- [ ] No debugging code or comments
- [ ] No unrelated changes

## 🎉 After Merge

Once your PR is merged:

1. **Delete your branch**:
   ```bash
   git branch -d your-branch
   git push origin --delete your-branch
   ```

2. **Update your fork**:
   ```bash
   git checkout main
   git pull upstream main
   git push origin main
   ```

3. **Celebrate!** 🎉 You've contributed to NeoFramework!

## 📚 Additional Resources

- [Git Best Practices](https://git-scm.com/book/en/v2)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [How to Write a Git Commit Message](https://chris.beams.io/posts/git-commit/)
- [Code Review Best Practices](https://google.github.io/eng-practices/review/)

---

Thank you for contributing to NeoFramework! Your pull requests help make the framework better for everyone. 🚀
