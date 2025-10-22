# TODO: Fix Admin Products CRUD and Enhance Filtering/Pagination

## Overview
This TODO tracks the implementation of fixes for add/edit/delete (AJAX compatibility, JSON responses) and enhancements for filtering/pagination (server-side search). Steps are sequential; each will be marked [x] upon completion.

## Steps

1. **[x] Update add.php**: Add AJAX detection (check HTTP_X_REQUESTED_WITH), return JSON on success/error instead of redirect for AJAX requests. Preserve existing redirect for non-AJAX. Add error logging to '../../logs/crud_errors.log' if query fails.

2. **[x] Update edit.php**: Similar to add.php - add AJAX detection, JSON responses, error logging.

3. **[x] Update delete.php**: Convert to AJAX partial (remove full HTML head/body, keep only content div/form). Add AJAX detection, JSON responses on POST, error logging. Ensure image deletion works.

4. **[x] Create logs directory**: If not exists, create '../../logs/' (project root/logs) for error logs.

5. **[x] Update list.php**: 
   - Add server-side search via GET['search'] (filter name LIKE '%term%' OR price LIKE '%term%'), integrate with category filter and pagination.
   - Update pagination links to include search and cat params.
   - Enhance JS: On search input (after debounce), if term > 2 chars, redirect to ?search=term&cat=... to trigger server-side; fallback to client-side.
   - Add handling for success/error messages from URL params (e.g., show toast if ?success=added).

6. **[x] Update dashboard.php**: 
   - Enhance bindAjaxLinks() to intercept form submissions in loaded content (preventDefault, AJAX POST to form action, handle JSON: show message, reload list.php on success).
   - Add global message display (e.g., toast div for success/error from JSON or URL params).
   - Ensure rebinding after loads.

7. **[x] Test Implementation**:
   - Use browser_action to launch http://localhost/SE_Final-Cart-Checkout/admin/dashboard.php.
   - Navigate to products/list.php, test add (fill form, submit, verify new product in list and DB).
   - Test edit (click edit, change, submit, verify update).
   - Test delete (click delete, confirm, verify removal).
   - Test filtering: Category select, search input (across pages), pagination.
   - Check console for JS errors, verify no PHP errors in logs.

8. **[x] Cleanup**: Remove schema_check.php from e:/DSA_Python if no longer needed. Suggest user checks phpMyAdmin for DB verification.

## Notes
- All changes use prepared statements and validation as-is.
- Assume XAMPP running on port 80; adjust if needed.
- After each step, verify file updates and test manually if possible.
