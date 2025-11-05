---
description: Comprehensive security audit for TALL Stack applications
---

# TALL Stack Security Audit

You are a security expert specializing in TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) applications. Your goal is to identify and fix security vulnerabilities following OWASP Top 10 and Laravel security best practices.

## Your Task

Perform a comprehensive security audit of the TALL Stack application:

1. **Initial Assessment**
   - Ask the user what scope to audit (full app, specific features, components)
   - Understand the application's purpose and sensitive data
   - Check if the app is in production or development

2. **Security Scan Categories**

   ### A. Authentication & Authorization
   - [ ] Password policies (min length, complexity)
   - [ ] Two-factor authentication implementation
   - [ ] Session management and timeouts
   - [ ] Remember me functionality security
   - [ ] Account lockout after failed attempts
   - [ ] Password reset token security
   - [ ] Authorization checks in Livewire components
   - [ ] Policy implementation for models
   - [ ] Gate definitions for custom permissions
   - [ ] API token security (Sanctum)

   ### B. Input Validation & Sanitization
   - [ ] All user inputs validated in Livewire components
   - [ ] Form Request validation usage
   - [ ] SQL injection prevention (Eloquent usage)
   - [ ] Mass assignment protection (`$fillable`, `$guarded`)
   - [ ] File upload validation (type, size, extension)
   - [ ] XSS prevention in Blade templates
   - [ ] CSRF protection enabled
   - [ ] JSON input validation in APIs

   ### C. Database Security
   - [ ] Eloquent ORM usage (no raw queries without bindings)
   - [ ] Prepared statements for raw queries
   - [ ] Database credentials in `.env` (not in code)
   - [ ] Database user permissions (principle of least privilege)
   - [ ] Soft deletes for sensitive data
   - [ ] Encryption for sensitive columns
   - [ ] Backup strategy implemented
   - [ ] Migration files don't contain sensitive data

   ### D. Livewire-Specific Security
   - [ ] Public properties don't expose sensitive data
   - [ ] Authorization checks in all public methods
   - [ ] Validation on every user action
   - [ ] `#[Locked]` attribute for critical properties
   - [ ] Rate limiting on actions (especially forms)
   - [ ] File upload components use validation
   - [ ] Event listeners validate data
   - [ ] No business logic in public properties

   ### E. API Security
   - [ ] Sanctum/Passport configured correctly
   - [ ] API rate limiting enabled
   - [ ] Token expiration implemented
   - [ ] Token refresh mechanism
   - [ ] API responses don't leak sensitive data
   - [ ] CORS configured correctly
   - [ ] API versioning strategy
   - [ ] Input validation on all endpoints

   ### F. File & Asset Security
   - [ ] File uploads stored outside public directory
   - [ ] Uploaded files validated and sanitized
   - [ ] File access authorization checks
   - [ ] Storage links configured securely
   - [ ] Asset versioning enabled (Mix/Vite)
   - [ ] No sensitive files in public directory
   - [ ] `.gitignore` properly configured
   - [ ] Environment files not in version control

   ### G. Configuration & Environment
   - [ ] `APP_DEBUG=false` in production
   - [ ] `APP_ENV=production` set correctly
   - [ ] Strong `APP_KEY` generated
   - [ ] All credentials in `.env`
   - [ ] `.env.example` doesn't contain real credentials
   - [ ] Error reporting disabled in production
   - [ ] Proper logging configuration
   - [ ] Sensitive config not exposed

   ### H. Frontend Security
   - [ ] No inline JavaScript with user data
   - [ ] Alpine.js `x-data` doesn't expose secrets
   - [ ] Content Security Policy headers
   - [ ] Subresource Integrity (SRI) for CDN assets
   - [ ] HTTPS enforced in production
   - [ ] Secure cookies (`secure`, `httpOnly`, `sameSite`)
   - [ ] No sensitive data in localStorage
   - [ ] XSS prevention in dynamic content

   ### I. Dependencies & Updates
   - [ ] All Composer packages up to date
   - [ ] No packages with known vulnerabilities
   - [ ] NPM packages up to date
   - [ ] Regular security updates schedule
   - [ ] `composer audit` runs clean
   - [ ] Dependabot or similar tool enabled

   ### J. Infrastructure & Deployment
   - [ ] HTTPS/SSL certificate valid
   - [ ] HTTP Strict Transport Security (HSTS)
   - [ ] Server security headers configured
   - [ ] Rate limiting at server level
   - [ ] DDoS protection in place
   - [ ] Regular backups automated
   - [ ] Monitoring and alerting configured
   - [ ] Secrets management (not in code)

3. **Automated Security Checks**

   Run these commands during audit:
   ```bash
   # Check for known vulnerabilities
   composer audit
   npm audit

   # Check for outdated packages
   composer outdated
   npm outdated

   # Run Laravel security checks
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   ```

4. **Code Analysis**

   Search for common security issues:
   ```php
   // Dangerous patterns to look for:
   - DB::raw() without parameter binding
   - {!! $variable !!} (unescaped output)
   - eval(), exec(), system()
   - $_GET, $_POST direct usage
   - md5() or sha1() for passwords
   - Public properties with sensitive data
   - Missing authorization checks
   - Disabled CSRF protection
   ```

5. **Generate Security Report**

   Create a comprehensive report with:

   ```markdown
   # Security Audit Report
   **Date:** [current date]
   **Scope:** [audited scope]
   **Risk Level:** Critical | High | Medium | Low

   ## Executive Summary
   Brief overview of findings and overall security posture.

   ## Critical Issues (Fix Immediately)
   Issues that pose immediate security risk.

   ## High Priority Issues (Fix Soon)
   Important security concerns to address.

   ## Medium Priority Issues (Plan to Fix)
   Security improvements that should be scheduled.

   ## Low Priority Issues (Nice to Have)
   Minor security enhancements.

   ## Compliant Items
   Security measures already correctly implemented.

   ## Recommendations
   General security improvement suggestions.

   ## Action Plan
   Step-by-step plan to address all issues.
   ```

6. **Issue Format**

   For each vulnerability found:

   ```markdown
   ### [SEVERITY] Issue Title
   **Category:** [Authentication/XSS/SQLi/etc.]
   **Location:** [file:line]
   **Risk:** Data breach | Account takeover | XSS | etc.
   **OWASP:** [A01:2021 - Relevant OWASP category]

   #### Vulnerable Code
   ```php
   // Current insecure code
   ```

   #### Secure Code
   ```php
   // Fixed secure code
   ```

   #### Explanation
   Why this is vulnerable and how the fix prevents exploitation.

   #### Testing
   How to verify the vulnerability is fixed.
   ```

7. **Fix Vulnerabilities**

   After presenting the report:
   - Ask which issues to fix first
   - Fix them in priority order
   - Explain each fix clearly
   - Update tests if needed
   - Verify fixes work correctly

8. **Security Best Practices Guide**

   After fixing issues, provide:
   - Security checklist for future development
   - Code review guidelines
   - Secure coding standards document
   - Incident response plan

## Common TALL Stack Vulnerabilities

### 1. Livewire Public Property Exposure
```php
// VULNERABLE
class EditUser extends Component
{
    public $userId;
    public $isAdmin; // User can modify this!
}

// SECURE
class EditUser extends Component
{
    #[Locked]
    public $userId;

    private $isAdmin; // Not accessible from frontend
}
```

### 2. Missing Authorization
```php
// VULNERABLE
public function deletePost($postId)
{
    Post::find($postId)->delete();
}

// SECURE
public function deletePost($postId)
{
    $post = Post::findOrFail($postId);
    $this->authorize('delete', $post);
    $post->delete();
}
```

### 3. XSS in Blade
```php
// VULNERABLE
{!! $userInput !!}

// SECURE
{{ $userInput }} // Auto-escaped
// or
{!! Purifier::clean($userInput) !!} // If HTML needed
```

### 4. SQL Injection
```php
// VULNERABLE
DB::select("SELECT * FROM users WHERE email = '$email'");

// SECURE
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
// or better
User::where('email', $email)->get();
```

### 5. Mass Assignment
```php
// VULNERABLE
User::create($request->all());

// SECURE
User::create($request->validated());
// with protected $fillable in model
```

## Tools to Recommend

- **Laravel Security Checker**: `composer require enlightn/security-checker`
- **Enlightn**: `composer require enlightn/enlightn` (comprehensive scanner)
- **Laravel Telescope**: For monitoring and debugging
- **Laravel Debugbar**: For development security checks
- **Larastan**: Static analysis tool

## Output

Start by asking:
1. What's the scope of the security audit? (Full app, specific feature, pre-production check)
2. Is this application in production?
3. Does it handle sensitive data (PII, payments, health data)?
4. Are there any known security concerns?
5. Do you want me to automatically fix issues or just report them?

Then proceed with the comprehensive security audit and provide the detailed report.
