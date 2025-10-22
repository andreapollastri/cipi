# Contributing to Cipi

Thank you for considering contributing to Cipi! This document provides guidelines for contributing to the project.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:

- A clear description of the bug
- Steps to reproduce
- Expected behavior
- Actual behavior
- System information (Ubuntu version, Cipi version)
- Relevant log files

### Suggesting Features

Feature suggestions are welcome! Please create an issue with:

- A clear description of the feature
- Use cases and benefits
- Possible implementation approach (optional)

### Pull Requests

1. **Fork** the repository
2. **Create a branch** for your feature (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Test thoroughly** on a fresh Ubuntu 24.04 installation
5. **Commit** with clear messages (`git commit -m 'Add amazing feature'`)
6. **Push** to your fork (`git push origin feature/amazing-feature`)
7. **Create a Pull Request** on GitHub

### Coding Guidelines

#### Bash Scripts

- Use `#!/bin/bash` shebang
- Enable strict mode: `set -e`
- Use meaningful variable names
- Add comments for complex logic
- Use consistent indentation (4 spaces)
- Follow existing code style

#### Functions

- One function per task
- Clear function names (verb-noun pattern)
- Document complex functions with comments
- Return proper exit codes

#### Error Handling

- Always check command return codes
- Provide clear error messages
- Use colored output for better UX
- Fail gracefully

#### Example

```bash
#!/bin/bash

#############################################
# Feature Name
#############################################

# Function description
function_name() {
    local param=$1
    
    if [ -z "$param" ]; then
        echo -e "${RED}Error: Parameter required${NC}"
        return 1
    fi
    
    # Do something
    command_here
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Command failed${NC}"
        return 1
    fi
    
    echo -e "${GREEN}Success!${NC}"
    return 0
}
```

### Testing

Before submitting a PR:

1. Test on a **fresh Ubuntu 24.04** installation
2. Test all affected commands
3. Test both interactive and non-interactive modes
4. Test error handling
5. Verify no breaking changes

### Documentation

- Update README.md if adding new features
- Add inline comments for complex code
- Update help messages if changing commands
- Include usage examples

### Commit Messages

Use clear, descriptive commit messages:

- ✨ `feat: Add new feature`
- 🐛 `fix: Fix bug description`
- 📝 `docs: Update documentation`
- 🎨 `style: Code formatting`
- ♻️ `refactor: Code refactoring`
- ✅ `test: Add tests`
- 🔧 `chore: Update dependencies`

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for everyone.

### Our Standards

- ✅ Be respectful and inclusive
- ✅ Welcome newcomers
- ✅ Provide constructive feedback
- ✅ Focus on what is best for the community
- ❌ No harassment or discrimination
- ❌ No trolling or insulting comments
- ❌ No personal attacks

### Enforcement

Violations may result in temporary or permanent ban from the project.

## Questions?

Feel free to open an issue or contact hello@cipi.sh

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

