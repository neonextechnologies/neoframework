# 🤝 Contributing Guidelines

Welcome to the NeoFramework community! We're excited that you're interested in contributing to the project. This guide will help you understand how to contribute effectively and ensure a smooth collaboration process.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Getting Started](#getting-started)
- [Issue Reporting](#issue-reporting)
- [Feature Requests](#feature-requests)
- [Documentation Contributions](#documentation-contributions)
- [Community Support](#community-support)

## 🌟 Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of experience level, background, or identity.

### Expected Behavior

- **Be Respectful**: Treat all community members with respect and kindness
- **Be Collaborative**: Work together and help others succeed
- **Be Professional**: Keep discussions focused and constructive
- **Be Patient**: Remember that everyone was a beginner once
- **Be Open-Minded**: Accept feedback graciously and consider different perspectives

### Unacceptable Behavior

- Harassment, discrimination, or offensive comments
- Trolling, insulting remarks, or personal attacks
- Publishing others' private information
- Spam or off-topic discussions
- Any conduct that would be inappropriate in a professional setting

### Enforcement

Violations of the code of conduct may result in:
1. A warning from maintainers
2. Temporary ban from the community
3. Permanent ban for repeated or severe violations

Report violations to: conduct@neoframework.com

## 🎯 How Can I Contribute?

There are many ways to contribute to NeoFramework:

### 1. Code Contributions

- Fix bugs and issues
- Implement new features
- Improve performance
- Refactor existing code
- Add tests

### 2. Documentation

- Fix typos and errors
- Improve clarity and examples
- Write tutorials and guides
- Translate documentation
- Create video tutorials

### 3. Testing

- Write unit tests
- Write integration tests
- Test new features
- Report bugs
- Verify bug fixes

### 4. Community Support

- Answer questions on GitHub Discussions
- Help users in Discord
- Write blog posts
- Share your experience
- Speak at conferences

### 5. Design

- Improve UI/UX
- Create graphics and diagrams
- Design website improvements
- Create promotional materials

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have:

```bash
# Required software
- PHP 8.1 or higher
- Composer
- Git
- A code editor (VS Code recommended)

# Optional but recommended
- Docker
- PHPUnit
- PHP CS Fixer
```

### Fork and Clone

1. **Fork the repository** on GitHub

2. **Clone your fork**:

```bash
git clone https://github.com/YOUR_USERNAME/neoframework.git
cd neoframework
```

3. **Add upstream remote**:

```bash
git remote add upstream https://github.com/neonextechnologies/neoframework.git
```

4. **Install dependencies**:

```bash
composer install
```

5. **Create a branch**:

```bash
git checkout -b feature/your-feature-name
```

### Development Setup

1. **Copy environment file**:

```bash
cp .env.example .env
```

2. **Configure your environment**:

```php
// .env
APP_ENV=local
APP_DEBUG=true
APP_KEY=your-app-key

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neoframework
DB_USERNAME=root
DB_PASSWORD=
```

3. **Run tests to ensure everything works**:

```bash
composer test
```

### Keeping Your Fork Updated

Regularly sync your fork with the upstream repository:

```bash
# Fetch upstream changes
git fetch upstream

# Merge upstream changes into your local main branch
git checkout main
git merge upstream/main

# Push updates to your fork
git push origin main
```

## 🐛 Issue Reporting

Good bug reports are invaluable to improving NeoFramework. Follow these guidelines:

### Before Submitting

1. **Search existing issues** to avoid duplicates
2. **Verify the bug** in the latest version
3. **Check if it's already fixed** in the development branch
4. **Gather information** about your environment

### Bug Report Template

Use this template when reporting bugs:

```markdown
## Bug Description
A clear and concise description of the bug.

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Execute '...'
4. See error

## Expected Behavior
What you expected to happen.

## Actual Behavior
What actually happened.

## Environment
- NeoFramework Version: [e.g., 1.5.0]
- PHP Version: [e.g., 8.2.0]
- Operating System: [e.g., Ubuntu 22.04]
- Web Server: [e.g., Nginx 1.22]
- Database: [e.g., MySQL 8.0]

## Code Sample
```php
// Minimal code to reproduce the issue
$app = new Application();
$result = $app->problematicMethod();
```

## Stack Trace
```
Full error message and stack trace
```

## Additional Context
Any other relevant information, screenshots, or logs.

## Possible Solution
(Optional) Suggest a fix or reason for the bug.
```

### Issue Labels

Maintainers will categorize issues with labels:

- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Documentation improvements
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention needed
- `question` - Further information requested
- `duplicate` - This issue already exists
- `wontfix` - This will not be worked on
- `priority:high` - Critical issue
- `priority:medium` - Important issue
- `priority:low` - Nice to have

## 💡 Feature Requests

We welcome feature requests! Here's how to submit them effectively:

### Before Requesting

1. **Search existing requests** to avoid duplicates
2. **Check the roadmap** to see if it's already planned
3. **Consider if it fits** NeoFramework's philosophy
4. **Think about alternatives** and trade-offs

### Feature Request Template

```markdown
## Feature Summary
A clear and concise description of the feature.

## Problem Statement
What problem does this feature solve?
Example: "I'm always frustrated when..."

## Proposed Solution
Describe your ideal solution.

## Code Example
```php
// How you envision using this feature
$feature = new Feature();
$feature->doSomething();
```

## Alternatives Considered
What other solutions have you considered?

## Benefits
- Who benefits from this feature?
- What use cases does it enable?
- How does it improve the framework?

## Drawbacks
- Are there any downsides?
- Does it add complexity?
- Are there performance implications?

## Implementation Ideas
(Optional) Suggestions for how to implement this.

## Related Issues
Links to related issues or discussions.
```

### Feature Evaluation Criteria

Maintainers evaluate features based on:

1. **Alignment** with framework philosophy
2. **Impact** on existing code and users
3. **Complexity** vs. benefit ratio
4. **Maintenance burden**
5. **Community demand**
6. **Breaking changes** required

### Feature Lifecycle

1. **Proposal**: Feature request submitted
2. **Discussion**: Community feedback and refinement
3. **Acceptance**: Maintainers approve the feature
4. **Implementation**: Code is written and tested
5. **Review**: Pull request review process
6. **Release**: Feature included in a release

## 📚 Documentation Contributions

Documentation is crucial for NeoFramework's success. Here's how to contribute:

### Types of Documentation

1. **API Documentation**
   - Class and method descriptions
   - Parameter explanations
   - Return value documentation
   - Usage examples

2. **Guides and Tutorials**
   - Getting started guides
   - Step-by-step tutorials
   - Best practices
   - Advanced topics

3. **Reference Documentation**
   - Configuration options
   - Available methods
   - Event lists
   - Error codes

### Documentation Standards

Follow these standards for consistency:

**Markdown Formatting**:

```markdown
# Main Heading (H1)

Introduction paragraph.

## Section Heading (H2)

Section content.

### Subsection (H3)

Subsection content.

#### Minor Heading (H4)

Minor section content.
```

**Code Examples**:

```markdown
\`\`\`php
// Always include context
use Neo\Foundation\Application;

// Use meaningful variable names
$application = new Application();

// Add comments for complex logic
$result = $application->processRequest($request);

// Show expected output
// Output: "Request processed successfully"
\`\`\`
```

**Cross-References**:

```markdown
See [Routing](routing.md) for more information.
Learn about [Middleware](../basics/middleware.md).
Check the [API Documentation](../../api/resources.md).
```

**Callouts and Notes**:

```markdown
> **Note**: This feature requires PHP 8.1 or higher.

> **Warning**: This method will be deprecated in version 2.0.

> **Tip**: Use dependency injection for better testability.
```

### Documentation Checklist

Before submitting documentation changes:

- [ ] Spelling and grammar checked
- [ ] Code examples tested and working
- [ ] Links verified
- [ ] Screenshots updated (if applicable)
- [ ] Table of contents updated
- [ ] Consistent formatting
- [ ] Clear and concise writing
- [ ] Appropriate level of detail

### Writing Style Guide

**Voice and Tone**:
- Use active voice: "Create a controller" not "A controller is created"
- Be conversational but professional
- Address the reader as "you"
- Use present tense: "The method returns" not "The method will return"

**Clarity**:
- Start with simple concepts, progress to complex
- Use examples liberally
- Define technical terms
- Break complex topics into digestible sections

**Consistency**:
- Use consistent terminology
- Follow existing patterns
- Match the framework's vocabulary
- Use NeoFramework-specific terms correctly

## 🤗 Community Support

Help make NeoFramework's community welcoming and helpful:

### Answering Questions

**On GitHub Discussions**:
- Search for existing answers first
- Provide complete, tested solutions
- Explain the reasoning behind your answer
- Link to relevant documentation
- Be patient with beginners

**Best Practices**:

```markdown
## Good Answer Example

To create a custom middleware, follow these steps:

1. Create a class that implements `MiddlewareInterface`:

\`\`\`php
use Neo\Http\Contracts\MiddlewareInterface;

class CustomMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        // Your logic here
        return $next($request);
    }
}
\`\`\`

2. Register it in your `app/AppModule.php`:

\`\`\`php
public function middleware(): array
{
    return [
        CustomMiddleware::class,
    ];
}
\`\`\`

For more details, see the [Middleware documentation](link).
```

### Code Reviews

When reviewing code:

1. **Be constructive**: Suggest improvements, don't just criticize
2. **Explain why**: Help contributors learn
3. **Praise good work**: Recognize quality contributions
4. **Ask questions**: Understand the contributor's reasoning
5. **Test the code**: Verify it works as expected
6. **Check documentation**: Ensure changes are documented

### Mentoring

Help new contributors get started:

- Mark issues as `good first issue`
- Provide guidance on complex issues
- Review PRs promptly
- Share knowledge and resources
- Celebrate first contributions

## 🎁 Recognition

We value all contributions! Contributors are recognized through:

- **Contributors List**: Featured in README.md
- **Release Notes**: Acknowledged in changelog
- **GitHub Profile**: Contributions visible on your profile
- **Community Shoutouts**: Recognition in community channels

## 📧 Contact

Need help or have questions?

- **GitHub Discussions**: [discussions](https://github.com/neonextechnologies/neoframework/discussions)
- **Discord**: [Join our server](https://discord.gg/neoframework)
- **Email**: contribute@neoframework.com
- **Twitter**: [@neoframework](https://twitter.com/neoframework)

## 📄 License

By contributing to NeoFramework, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to NeoFramework! Your efforts help make the framework better for everyone. 🚀
