# Contributing to VentlyX

Thank you for your interest in contributing to VentlyX! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/ventlyx.git`
3. Create a new branch: `git checkout -b feature/your-feature-name`
4. Make your changes following our coding standards

## Coding Standards

### PHP (Backend)

- Follow PSR-12 coding style
- Use PHP 8.2+ features where applicable
- Include type declarations for method parameters and return types
- Keep functions and methods focused and small

#### Naming Conventions

- Classes: PascalCase (e.g., `EventController`)
- Methods/Functions: camelCase (e.g., `getEventDetails()`)
- Variables: camelCase (e.g., `$eventData`)
- Constants: UPPER_SNAKE_CASE (e.g., `API_VERSION`)
- Database Tables: snake_case, plural (e.g., `events`)
- Database Columns: snake_case (e.g., `first_name`)

### Vue.js (Frontend)

- Use TypeScript for all new components
- Follow Vue.js 3 Composition API patterns
- Implement proper component organization
- Use Tailwind CSS for styling

## Testing Requirements

- Backend: PHPUnit tests required for new features
- Frontend: Vitest for unit tests, Playwright for E2E tests
- Maintain or improve current test coverage

## Commit Guidelines

- Use present tense ("Add feature" not "Added feature")
- First line: summary (max 50 characters)
- Include detailed description if needed
- Reference issue numbers

Example:
```
Add event registration confirmation emails

- Send confirmation email when user registers for event
- Include QR code in email for check-in
- Add email template with event details

Fixes #123
```

## Pull Request Process

1. Update documentation for any new features
2. Add or update tests as needed
3. Ensure all tests pass locally
4. Update the README.md if needed
5. Create a pull request with a clear description

## Code Review Checklist

- [ ] Code follows project standards
- [ ] All tests pass
- [ ] New features have tests
- [ ] Documentation is updated
- [ ] No security vulnerabilities
- [ ] No debug code remains
- [ ] Proper error handling implemented

## Development Setup

See [RUNNING.md](RUNNING.md) for detailed setup instructions.

## Questions or Problems?

- Check our [Troubleshooting Guide](backend/docs/TROUBLESHOOTING.md)
- Open an issue for bugs or feature requests
- Contact the development team at dev@ventlyx.com

## License

By contributing to VentlyX, you agree that your contributions will be licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.