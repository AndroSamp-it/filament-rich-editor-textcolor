# Contributing Guide

Thank you for your interest in improving this project! We welcome all suggestions and contributions.

## Reporting Issues

If you found a bug or problem:

1. Check if a similar issue has already been created
2. Create a new issue with detailed description:
   - Package version
   - PHP version
   - Laravel/Filament version
   - Steps to reproduce the issue
   - Expected and actual behavior
   - Screenshots (if applicable)

## Suggesting Enhancements

Have an idea for a new feature?

1. Create an issue describing the suggestion
2. Explain what problem your suggestion solves
3. Provide usage examples

## Development Process

### Environment Setup

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/your-username/filament-rich-editor-textcolor.git
cd filament-rich-editor-textcolor
```

3. Install dependencies:
```bash
composer install
npm install
```

### Making Changes

1. Create a new branch:
```bash
git checkout -b feature/your-feature
```

2. Make your code changes

3. If you modified JavaScript:
```bash
npm run build
```

4. Commit your changes:
```bash
git commit -m "Description of your changes"
```

## Coding Standards

- **PHP**: Follow PSR-12
- **JavaScript**: Use modern ES6+ syntax
- **Comments**: Write clear comments in English
- **Naming**: Use clear and descriptive variable and function names

## Pull Request Process

1. Ensure your code works
2. Update documentation if necessary
3. Create a Pull Request with detailed description:
   - What was changed
   - Why it's necessary
   - How to test the changes

4. Wait for code review

## Project Structure

```
/src - PHP plugin classes
/resources
  /js - JavaScript code for TipTap
  /css - Styles
  /lang - Translations
```

## Questions?

If you have questions, create an issue with the "question" label.

Thank you for contributing! 🎉
