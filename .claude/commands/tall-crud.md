---
description: Generate a complete CRUD interface for a model with TALL Stack
---

You are tasked with creating a complete CRUD (Create, Read, Update, Delete) interface using the TALL Stack. Follow these steps:

1. Ask the user for:
   - Model name (e.g., Post, Product, User)
   - Fields with their types and validation rules
   - Any relationships to other models
   - Whether to include soft deletes

2. Create the model and migration:
   ```bash
   php artisan make:model ModelName -mfc
   ```

3. Generate the migration with appropriate columns:
   - Use correct column types
   - Add indexes for frequently queried columns
   - Set up foreign keys for relationships
   - Include timestamps and soft deletes if requested

4. Update the Model class:
   - Define fillable/guarded properties
   - Add relationships
   - Include casts for dates, booleans, JSON
   - Add any accessors or mutators needed

5. Create Livewire components:
   - **List Component** (ModelList.php)
     - Pagination
     - Search functionality
     - Sorting
     - Delete confirmation

   - **Create/Edit Form Component** (ModelForm.php)
     - Validation rules with #[Rule] attributes
     - Save method
     - Update method
     - Proper error handling

   - **Show Component** (optional, ModelShow.php)
     - Display single record
     - Edit/Delete actions

6. Create routes in `routes/web.php`:
   ```php
   Route::get('/models', ModelList::class)->name('models.index');
   Route::get('/models/create', ModelForm::class)->name('models.create');
   Route::get('/models/{model}/edit', ModelForm::class)->name('models.edit');
   ```

7. Style all views with Tailwind CSS:
   - Responsive tables for list view
   - Well-designed forms with proper spacing
   - Action buttons with appropriate colors
   - Loading states
   - Empty states
   - Error/success messages

8. Add authorization (if needed):
   - Create policy: `php artisan make:policy ModelPolicy --model=Model`
   - Implement policy methods (view, create, update, delete)
   - Apply authorization in components

9. Create factory and seeder for testing:
   - Define factory with realistic fake data
   - Create seeder for initial data

10. Show the user:
    - All generated files and their locations
    - How to run migrations
    - How to seed the database
    - URLs for accessing the CRUD interface
    - Any additional configuration needed

Remember to follow TALL Stack best practices throughout!
