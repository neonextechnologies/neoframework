# Plugin Distribution

## Overview

This guide covers packaging, versioning, publishing, and distributing NeoFramework plugins to make them available for others to use.

## Preparing for Distribution

### Plugin Checklist

Before distributing your plugin, ensure:

- [ ] Complete `plugin.json` manifest
- [ ] Comprehensive README.md
- [ ] License file (LICENSE.md)
- [ ] Changelog (CHANGELOG.md)
- [ ] Configuration files with sensible defaults
- [ ] Database migrations (if applicable)
- [ ] Comprehensive tests
- [ ] Documentation
- [ ] Example usage
- [ ] Security review completed

### Plugin Structure

Ensure your plugin follows the standard structure:

```
my-plugin/
├── plugin.json              # Required
├── Plugin.php               # Required
├── README.md                # Required
├── LICENSE.md               # Required
├── CHANGELOG.md             # Recommended
├── composer.json            # Recommended
├── .gitignore
├── config/
│   └── config.php
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Middleware/
├── routes/
│   └── web.php
├── resources/
│   ├── views/
│   ├── lang/
│   └── assets/
├── database/
│   ├── migrations/
│   └── seeders/
├── tests/
│   ├── Unit/
│   └── Feature/
└── docs/
    └── installation.md
```

## Versioning

### Semantic Versioning

Follow [Semantic Versioning](https://semver.org/) (MAJOR.MINOR.PATCH):

- **MAJOR**: Incompatible API changes
- **MINOR**: Backwards-compatible functionality additions
- **PATCH**: Backwards-compatible bug fixes

```json
{
    "version": "1.2.3"
}
```

### Version Tags

Tag releases in git:

```bash
# Create version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0

# List tags
git tag -l

# Delete tag
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
```

### Changelog

Maintain a CHANGELOG.md file:

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- New feature description

### Changed
- Changed feature description

### Fixed
- Bug fix description

## [1.0.0] - 2024-01-15

### Added
- Initial release
- Basic functionality
- Documentation

### Fixed
- Initial bug fixes

[Unreleased]: https://github.com/username/my-plugin/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/username/my-plugin/releases/tag/v1.0.0
```

## Documentation

### README.md

Create comprehensive README:

```markdown
# My Plugin

Brief description of your plugin.

## Features

- Feature 1
- Feature 2
- Feature 3

## Requirements

- PHP >= 8.1
- NeoFramework >= 1.0
- Other dependencies

## Installation

### Via Composer

\`\`\`bash
composer require vendor/my-plugin
\`\`\`

### Via CLI

\`\`\`bash
php neo plugin:install my-plugin
\`\`\`

### Manual Installation

1. Download the latest release
2. Extract to `plugins/my-plugin`
3. Run `php neo plugin:activate my-plugin`

## Configuration

Publish configuration file:

\`\`\`bash
php neo vendor:publish --tag=my-plugin-config
\`\`\`

Edit `config/my-plugin.php`:

\`\`\`php
return [
    'api_key' => env('MY_PLUGIN_API_KEY'),
    // Other settings
];
\`\`\`

## Usage

### Basic Usage

\`\`\`php
use MyPlugin\Services\MyService;

$service = app(MyService::class);
$result = $service->doSomething();
\`\`\`

### Advanced Usage

\`\`\`php
// Advanced examples
\`\`\`

## Commands

\`\`\`bash
# Sync data
php neo my-plugin:sync

# Other commands
php neo my-plugin:command
\`\`\`

## API Reference

See [API Documentation](docs/api.md) for detailed API reference.

## Testing

\`\`\`bash
# Run tests
composer test

# With coverage
composer test:coverage
\`\`\`

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines.

## Security

If you discover a security vulnerability, please email security@example.com.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

This plugin is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- [Your Name](https://github.com/username)
- [All Contributors](../../contributors)

## Support

- [Documentation](https://docs.example.com)
- [Issues](https://github.com/username/my-plugin/issues)
- [Discussions](https://github.com/username/my-plugin/discussions)
```

### License

Include appropriate license file (LICENSE.md):

```markdown
MIT License

Copyright (c) 2024 Your Name

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Composer Package

### composer.json

Create a composer.json file:

```json
{
    "name": "vendor/my-plugin",
    "description": "A plugin for NeoFramework",
    "keywords": ["neoframework", "plugin", "my-plugin"],
    "license": "MIT",
    "type": "neoframework-plugin",
    "authors": [
        {
            "name": "Your Name",
            "email": "you@example.com",
            "homepage": "https://example.com",
            "role": "Developer"
        }
    ],
    "homepage": "https://github.com/vendor/my-plugin",
    "support": {
        "issues": "https://github.com/vendor/my-plugin/issues",
        "source": "https://github.com/vendor/my-plugin",
        "docs": "https://docs.example.com/my-plugin"
    },
    "require": {
        "php": ">=8.1",
        "neoframework/framework": "^1.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "mockery/mockery": "^1.5"
    },
    "autoload": {
        "psr-4": {
            "MyPlugin\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "MyPlugin\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "test:coverage": "phpunit --coverage-html coverage"
    },
    "extra": {
        "neoframework": {
            "providers": [
                "MyPlugin\\MyPluginServiceProvider"
            ]
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### Publishing to Packagist

1. **Create Account**: Register at [packagist.org](https://packagist.org)

2. **Submit Package**: 
   - Click "Submit"
   - Enter repository URL: `https://github.com/vendor/my-plugin`
   - Click "Check"

3. **Auto-Update Hook**:
   ```bash
   # GitHub Webhook
   https://packagist.org/api/github?username=YOUR_USERNAME
   
   # Manual update
   curl -XPOST -H'content-type:application/json' \
     'https://packagist.org/api/update-package?username=USERNAME&apiToken=API_TOKEN' \
     -d'{"repository":{"url":"https://packagist.org/packages/vendor/my-plugin"}}'
   ```

## Distribution Channels

### GitHub Releases

Create releases on GitHub:

```bash
# Create release
gh release create v1.0.0 \
  --title "Version 1.0.0" \
  --notes "Release notes here"

# With assets
gh release create v1.0.0 \
  --title "Version 1.0.0" \
  --notes-file CHANGELOG.md \
  my-plugin-v1.0.0.zip
```

### Plugin Marketplace

Submit to NeoFramework plugin marketplace:

1. Create account on marketplace
2. Submit plugin information
3. Upload plugin package
4. Wait for approval
5. Respond to review feedback

### Direct Download

Provide direct download link:

```bash
# Create distribution package
php neo plugin:package my-plugin

# Creates: my-plugin-v1.0.0.zip
```

## Installation Methods

### Via Composer

Users can install via Composer:

```bash
composer require vendor/my-plugin
```

### Via CLI

Users can install via Neo CLI:

```bash
# From Packagist
php neo plugin:install vendor/my-plugin

# From GitHub
php neo plugin:install https://github.com/vendor/my-plugin

# From ZIP
php neo plugin:install /path/to/plugin.zip
```

### Manual Installation

Provide manual installation instructions:

```bash
# 1. Download plugin
wget https://github.com/vendor/my-plugin/archive/v1.0.0.zip

# 2. Extract to plugins directory
unzip v1.0.0.zip -d plugins/my-plugin

# 3. Install dependencies
cd plugins/my-plugin
composer install --no-dev

# 4. Activate plugin
php neo plugin:activate my-plugin
```

## Updates and Maintenance

### Automated Updates

Support automated updates:

```php
<?php

// In Plugin class
public function checkForUpdates(): ?string
{
    $response = Http::get('https://api.example.com/plugins/my-plugin/latest');
    $latest = $response->json()['version'];
    
    if (version_compare($latest, $this->getVersion(), '>')) {
        return $latest;
    }
    
    return null;
}

public function update(string $version): bool
{
    // Download and install update
    $package = $this->downloadUpdate($version);
    return $this->installUpdate($package);
}
```

### Migration Path

Provide migration paths for major updates:

```php
<?php

// In Plugin class
public function migrate(string $from, string $to): void
{
    // v1.x to v2.x migration
    if (version_compare($from, '2.0.0', '<') && 
        version_compare($to, '2.0.0', '>=')) {
        $this->migrateToV2();
    }
}

protected function migrateToV2(): void
{
    // Update database schema
    // Migrate configuration
    // Update stored data
}
```

## Support and Documentation

### Documentation Site

Create documentation site:

```bash
# Using GitBook
npm install -g gitbook-cli
gitbook init
gitbook serve

# Using VuePress
npm install -g vuepress
vuepress dev docs
```

### Issue Templates

Create issue templates in `.github/ISSUE_TEMPLATE/`:

**bug_report.md**:
```markdown
---
name: Bug report
about: Create a report to help us improve
title: '[BUG] '
labels: bug
assignees: ''
---

**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
 - NeoFramework Version: [e.g. 1.0.0]
 - PHP Version: [e.g. 8.1]
 - Plugin Version: [e.g. 1.0.0]

**Additional context**
Any other context about the problem.
```

**feature_request.md**:
```markdown
---
name: Feature request
about: Suggest an idea for this plugin
title: '[FEATURE] '
labels: enhancement
assignees: ''
---

**Is your feature request related to a problem?**
A clear description of the problem.

**Describe the solution you'd like**
What you want to happen.

**Describe alternatives you've considered**
Alternative solutions or features.

**Additional context**
Any other context or screenshots.
```

### Contributing Guide

Create CONTRIBUTING.md:

```markdown
# Contributing

Thank you for considering contributing to My Plugin!

## Development Setup

\`\`\`bash
git clone https://github.com/vendor/my-plugin.git
cd my-plugin
composer install
\`\`\`

## Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Coding Standards

- Follow PSR-12 coding style
- Write tests for new features
- Update documentation
- Add changelog entry

## Running Tests

\`\`\`bash
composer test
\`\`\`

## Code of Conduct

Be respectful and inclusive.
```

## Marketing and Promotion

### Plugin Page

Create dedicated plugin page with:

- Clear description
- Feature list
- Screenshots/demos
- Video tutorial
- Pricing (if commercial)
- Documentation link
- Support channels

### Social Media

Promote your plugin:

- Twitter/X announcement
- Reddit post
- Dev.to article
- YouTube tutorial
- Blog post

### Community Engagement

- Answer questions on forums
- Respond to issues quickly
- Accept pull requests
- Regular updates
- Newsletter for updates

## Monetization

### Free vs. Paid

**Free Plugin**:
- MIT or GPL license
- Open source on GitHub
- Community support

**Paid Plugin**:
- Commercial license
- Private repository
- Premium support
- Additional features

### Licensing Models

```php
<?php

// License validation
public function validateLicense(string $key): bool
{
    $response = Http::post('https://api.example.com/validate', [
        'license_key' => $key,
        'domain' => request()->getHost(),
    ]);
    
    return $response->successful() && $response->json()['valid'];
}
```

## Security

### Security Policy

Create SECURITY.md:

```markdown
# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

Please email security@example.com with:

- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We'll respond within 48 hours.
```

### Code Signing

Sign releases:

```bash
# GPG signing
gpg --armor --detach-sign my-plugin-v1.0.0.zip

# Verify
gpg --verify my-plugin-v1.0.0.zip.asc my-plugin-v1.0.0.zip
```

## Best Practices

1. **Version Everything**: Tag all releases
2. **Document Changes**: Maintain changelog
3. **Test Thoroughly**: High test coverage
4. **Respond Quickly**: Address issues promptly
5. **Keep Dependencies Updated**: Regular updates
6. **Follow Standards**: PSR standards
7. **Provide Examples**: Clear usage examples
8. **Support Users**: Active support channels
9. **Market Effectively**: Promote your plugin
10. **License Clearly**: Clear licensing terms

## Next Steps

- Return to [Plugin Introduction](introduction.md)
- Review [Plugin Development](development.md)
- Study [Plugin API Reference](plugin-api.md)
