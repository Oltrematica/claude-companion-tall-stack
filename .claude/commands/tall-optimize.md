---
description: Analyze and optimize a TALL Stack application for performance
---

You are tasked with analyzing and optimizing a TALL Stack application. Follow this systematic approach:

## 1. Database & Query Optimization

### Check for N+1 Problems
- Search for Eloquent queries in Livewire components and controllers
- Look for loops that make database queries
- Suggest eager loading with `with()` or `load()`
- Recommend using `withCount()` for counting relationships

### Analyze Queries
- Use Laravel Debugbar or Telescope data if available
- Look for:
  - Queries without indexes
  - SELECT * queries that could be optimized
  - Missing database indexes on foreign keys
  - Inefficient WHERE clauses

### Database Optimization
- Review migrations for missing indexes
- Suggest composite indexes for common queries
- Recommend database caching where appropriate
- Check for proper use of database transactions

## 2. Livewire Component Optimization

### Component Analysis
- Check if components are too large (split if needed)
- Look for missing `#[Computed]` properties on expensive operations
- Verify proper use of `#[Lazy]` for heavy components
- Check for unnecessary re-renders

### Data Loading
- Suggest lazy loading for initial page load
- Recommend pagination over loading all records
- Check for proper use of `wire:key` in lists
- Look for opportunities to use `wire:model.blur` or `.debounce`

### Property Optimization
- Check for large arrays in public properties
- Suggest using `#[Locked]` for properties that shouldn't change
- Recommend moving heavy logic to computed properties
- Look for properties that could be moved to the database

## 3. Frontend Optimization

### Tailwind CSS
- Check if PurgeCSS is configured correctly
- Verify production build is minified
- Look for excessive custom CSS (should use utilities)
- Check for unused Tailwind plugins

### Alpine.js
- Look for complex Alpine logic that should be in Livewire
- Check for memory leaks in Alpine components
- Verify proper use of `x-cloak` to prevent FOUC
- Look for opportunities to use `x-if` instead of `x-show`

### Assets
- Check if Vite is configured for production builds
- Look for unoptimized images
- Verify lazy loading for images below the fold
- Check for unused JavaScript/CSS

## 4. Laravel Optimization

### Caching
- Check for opportunities to cache:
  - Database queries (query cache)
  - Computed values (application cache)
  - Views (view cache)
  - Routes (route cache)
  - Config (config cache)

### Queue Jobs
- Look for slow operations in controllers/components
- Suggest moving to queued jobs:
  - Email sending
  - File processing
  - API calls
  - Report generation

### Session & Cache Drivers
- Recommend Redis over file driver for session/cache
- Check session configuration
- Verify cache driver is appropriate for environment

## 5. Code Quality

### Follow Best Practices
- Check for fat controllers (move logic to services)
- Look for repeated code (DRY principle)
- Verify proper use of form requests
- Check for missing validation

### Error Handling
- Look for missing try-catch blocks
- Check for proper error logging
- Verify user-friendly error messages

## 6. Provide Report

After analysis, provide:

1. **Critical Issues** - Must be fixed
   - Security vulnerabilities
   - Major performance bottlenecks
   - Database issues

2. **High Priority** - Should be fixed soon
   - N+1 queries
   - Missing indexes
   - Inefficient Livewire components

3. **Medium Priority** - Nice to have
   - Code organization
   - Minor optimizations
   - Caching opportunities

4. **Low Priority** - Future improvements
   - Code style
   - Documentation
   - Test coverage

For each issue found:
- Explain the problem
- Show the current code
- Provide optimized code
- Explain the performance impact
- Give implementation priority

Ask the user if they want you to:
- Fix all issues automatically
- Fix specific priority levels
- Fix specific issues only
- Just provide the report
