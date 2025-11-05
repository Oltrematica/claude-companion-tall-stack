---
description: Create a new Livewire component with Tailwind styling
---

You are tasked with creating a new Livewire component. Follow these steps:

1. Ask the user for:
   - Component name (e.g., UserProfile, PostList, etc.)
   - Component type (form, list/table, modal, card, or custom)
   - Any specific features or requirements

2. Generate the component using:
   ```bash
   php artisan make:livewire ComponentName
   ```

3. Based on the component type, implement:

   **For Form Components:**
   - Add validation rules using #[Rule] attributes
   - Implement save/update methods
   - Add proper error handling
   - Include loading states

   **For List/Table Components:**
   - Add WithPagination trait
   - Implement search functionality with #[Url]
   - Add sorting capabilities
   - Include empty states

   **For Modal Components:**
   - Add showModal property
   - Implement event listeners
   - Add transition states
   - Include cancel/confirm actions

   **For Card Components:**
   - Accept data via mount()
   - Keep it presentational
   - Add proper Tailwind styling

4. Style the component view with Tailwind CSS following TALL Stack best practices

5. If the component needs interactivity, add appropriate Alpine.js directives

6. Show the user:
   - The generated files and their locations
   - How to use the component in Blade templates
   - Any additional setup required

Remember to follow Laravel and Livewire conventions!
