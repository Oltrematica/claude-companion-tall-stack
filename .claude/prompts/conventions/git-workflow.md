# Git Workflow for TALL Stack Projects

Standard Git workflow and conventions for team collaboration on TALL Stack projects.

## Branch Strategy (Git Flow)

### Main Branches

```bash
main (or master)    # Production-ready code
develop             # Integration branch for features
```

### Supporting Branches

```bash
feature/*           # New features
bugfix/*           # Bug fixes
hotfix/*           # Urgent production fixes
release/*          # Release preparation
```

## Branch Naming

```bash
# Format: type/short-description-in-kebab-case

# Features
feature/user-authentication
feature/product-search
feature/payment-integration

# Bug fixes
bugfix/fix-login-redirect
bugfix/correct-price-calculation

# Hotfixes
hotfix/security-patch
hotfix/payment-gateway-error

# Releases
release/v1.2.0
release/v2.0.0-beta
```

## Commit Message Format

### Structure

```
type(scope): subject

body (optional)

footer (optional)
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, no logic change)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Build process or auxiliary tool changes
- `ci`: CI/CD changes

### Examples

```bash
# Feature
feat(auth): add two-factor authentication

# Bug fix
fix(payment): resolve Stripe webhook processing

# Multiple paragraphs
feat(posts): add post scheduling feature

Allow users to schedule posts for future publication.
Includes date/time picker and automatic publishing job.

Closes #123

# Breaking change
feat(api)!: change authentication to OAuth2

BREAKING CHANGE: API now requires OAuth2 tokens instead of API keys.
Migration guide: docs/migration/oauth2.md
```

## Workflow

### 1. Start New Feature

```bash
# Update develop branch
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/user-profile

# Work on feature
# ... make changes ...

# Stage and commit
git add .
git commit -m "feat(profile): add user profile page"

# Push to remote
git push origin feature/user-profile
```

### 2. Keep Branch Updated

```bash
# Regularly sync with develop
git checkout develop
git pull origin develop

git checkout feature/user-profile
git rebase develop

# Or merge (team preference)
git merge develop
```

### 3. Create Pull Request

```markdown
## Description
Brief description of what this PR does.

## Changes
- Added user profile page
- Created ProfileController
- Added profile routes
- Added tests

## Testing
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing completed
- [ ] No breaking changes

## Screenshots (if applicable)
[Add screenshots here]

## Related Issues
Closes #123
```

### 4. Code Review Checklist

**Before requesting review:**
- [ ] Tests pass locally
- [ ] Code follows style guidelines (run Pint)
- [ ] No console errors
- [ ] Updated documentation
- [ ] Removed debug code
- [ ] Self-reviewed the code

**Reviewer checklist:**
- [ ] Code is readable and maintainable
- [ ] Tests cover new functionality
- [ ] No security vulnerabilities
- [ ] Follows TALL Stack conventions
- [ ] Database migrations are reversible
- [ ] No N+1 queries introduced

### 5. Merge Strategy

```bash
# Squash and merge (recommended for feature branches)
# Creates clean, linear history

# Rebase and merge (for small, atomic commits)
# Preserves individual commit history

# Merge commit (for release branches)
# Preserves branch context
```

## Hotfix Workflow

```bash
# Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/critical-bug

# Fix and commit
git commit -m "fix: resolve critical payment bug"

# Merge to main
git checkout main
git merge hotfix/critical-bug
git tag v1.2.1
git push origin main --tags

# Also merge to develop
git checkout develop
git merge hotfix/critical-bug
git push origin develop

# Delete hotfix branch
git branch -d hotfix/critical-bug
```

## Release Workflow

```bash
# Create release branch from develop
git checkout -b release/v1.2.0 develop

# Bump version, update changelog
# ... final testing and bug fixes ...

# Merge to main
git checkout main
git merge release/v1.2.0
git tag -a v1.2.0 -m "Release version 1.2.0"
git push origin main --tags

# Merge back to develop
git checkout develop
git merge release/v1.2.0
git push origin develop

# Delete release branch
git branch -d release/v1.2.0
```

## Best Practices

### Commit Frequently

```bash
# ✅ Small, focused commits
git commit -m "feat(auth): add login form"
git commit -m "feat(auth): add authentication logic"
git commit -m "feat(auth): add tests for login"

# ❌ Large, unfocused commits
git commit -m "add authentication, fix bugs, update styles"
```

### Write Meaningful Messages

```bash
# ✅ Clear and descriptive
git commit -m "fix(cart): prevent duplicate items when adding rapidly"

# ❌ Vague
git commit -m "fix bug"
git commit -m "update"
git commit -m "wip"
```

### Keep Commits Atomic

```bash
# ✅ One logical change per commit
git commit -m "feat(posts): add post model and migration"
git commit -m "feat(posts): add post controller"

# ❌ Multiple unrelated changes
git commit -m "add posts, fix user bug, update styles"
```

## Git Hooks

### Pre-commit Hook

```bash
#!/bin/sh
# .git/hooks/pre-commit

# Run Laravel Pint
./vendor/bin/pint

# Run tests
php artisan test

# If tests fail, prevent commit
if [ $? -ne 0 ]; then
    echo "Tests failed. Commit aborted."
    exit 1
fi
```

### Commit Message Hook

```bash
#!/bin/sh
# .git/hooks/commit-msg

# Validate commit message format
commit_msg=$(cat "$1")

if ! echo "$commit_msg" | grep -qE '^(feat|fix|docs|style|refactor|perf|test|chore|ci)(\(.+\))?: .+'; then
    echo "Error: Invalid commit message format"
    echo "Format: type(scope): subject"
    echo "Example: feat(auth): add login page"
    exit 1
fi
```

## Useful Git Commands

### Undo Changes

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1

# Undo specific file
git checkout -- filename.php

# Unstage file
git reset HEAD filename.php
```

### Stashing

```bash
# Save work in progress
git stash

# List stashes
git stash list

# Apply latest stash
git stash pop

# Apply specific stash
git stash apply stash@{1}
```

### Interactive Rebase

```bash
# Rewrite last 3 commits
git rebase -i HEAD~3

# Options:
# pick - keep commit
# reword - change commit message
# squash - combine with previous
# drop - remove commit
```

### Cherry-pick

```bash
# Apply specific commit to current branch
git cherry-pick abc123
```

## .gitignore for TALL Stack

```bash
# Laravel
/node_modules
/public/hot
/public/storage
/public/build
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log

# IDE
/.idea
/.vscode
*.swp
*.swo
*.swn
.DS_Store

# Testing
/coverage

# Deployment
/deploy.php
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        run: php artisan test

      - name: Run Pint
        run: ./vendor/bin/pint --test
```

## Team Guidelines

1. **Pull before push**: Always pull latest changes before pushing
2. **Review your own PR**: Self-review before requesting team review
3. **Respond to feedback**: Address review comments promptly
4. **Delete merged branches**: Keep repository clean
5. **Update regularly**: Sync feature branches with develop frequently
6. **Communicate**: Use PR descriptions and commit messages effectively
