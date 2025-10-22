# TODO: Fix Product CRUD Connection Errors

## Current Work
- Diagnosed AJAX form submission path issues causing fetch failures (likely appearing as connection errors).
- Forms in add.php and edit.php use relative actions that misresolve when loaded via dashboard AJAX.

## Key Technical Concepts
- PHP/MySQL for backend CRUD.
- AJAX with Fetch API for dynamic form handling in admin dashboard.
- Relative vs. absolute paths in web apps (XAMPP local setup).

## Relevant Files and Code
- admin/products/add.php: Form has action="add.php" – remove to use JS-set path.
- admin/products/edit.php: Form has action="edit.php?id=..." – remove to use JS-set path (preserves ?id= via page param).
- admin/products/delete.php: Already correct (no action).
- admin/products/list.php: JS bindForms() sets action=page if unset – will now apply to all.

## Problem Solving
- Issue: Incorrect POST URLs lead to 404/fetch errors.
- Solution: Leverage existing JS to set correct action paths dynamically.

## Pending Tasks and Next Steps
1. Edit add.php: Remove action="add.php" from form. [Pending]
   - Exact change: From `<form method="post" action="add.php" enctype="multipart/form-data" ...>` to `<form method="post" enctype="multipart/form-data" ...>`.
2. Edit edit.php: Remove action="edit.php?id=<?= $product['product_id'] ?>" from form. [Pending]
   - Exact change: From `<form method="post" action="edit.php?id=<?= $product['product_id'] ?>" enctype="multipart/form-data" ...>` to `<form method="post" enctype="multipart/form-data" ...>`.
   - Note: ?id= preserved via loadPage('products/edit.php?id=...'), so $_GET['id'] still available on POST.
3. Test: After edits, perform add/edit/delete via dashboard; confirm no errors and success messages appear.
4. If logs show DB errors post-fix, check database/tables (but unlikely).

User quote: "Tại sao khi toi thực hiện các chức năng như là edit, delete, add, create, của mục "Quản lý sản phẩm" đều hiển thị thông báo "lỗi kết nối....". Bạn có thẻ sửa cho tôi"
