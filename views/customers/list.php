<?php // views/customers/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="toolbar">
    <div class="actions-bar">
        <a href="index.php?page=customers&action=add" class="button-link add-btn">إضافة عميل</a>
        <a href="index.php?page=customers&action=add&main_account=true" class="button-link" style="background-color:var(--info-color);">إنشاء حساب رئيسي</a>
        <?php if (check_permission('manage_customers')): ?>
            <a href="index.php?page=customers&action=import" class="button-link" style="background-color:#fd7e14;">استيراد من Excel</a>
        <?php endif; ?>
    </div>
    
    <div class="search-form">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="customers">
            <input type="text" name="search" placeholder="ابحث بالاسم, الرمز, المندوب..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
            <button type="submit" class="button-link search-btn">بحث</button>
            <?php if(!empty($search_term)): ?>
                <a href="index.php?page=customers" class="button-link" style="background-color: #777;">مسح</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>الرمز</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الحساب الرئيسي</th>
                <th>المندوب</th>
                <th>الحالة</th>
                <th>الفروع</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $cust_item): ?>
            <tr class="<?php echo $cust_item['is_main_account'] ? 'main-account-row' : ''; echo $cust_item['status'] == 'inactive' ? ' inactive-row' : ''; ?>">
                <td><?php echo htmlspecialchars($cust_item['customer_code']); ?></td>
                <td>
                    <?php echo htmlspecialchars($cust_item['name']); ?>
                    <?php if ($cust_item['is_main_account']): ?> <span class="badge-main">(رئيسي)</span><?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($cust_item['category_name'] ?? '-'); ?></td>
                <td>
                    <?php if ($cust_item['main_account_id']): ?>
                        <a href="index.php?page=customers&action=edit&id=<?php echo $cust_item['main_account_id']; ?>"><?php echo htmlspecialchars($cust_item['main_account_name_display'] ?? '-'); ?></a>
                    <?php else: echo '-'; endif; ?>
                </td>
                <td><?php echo htmlspecialchars($cust_item['representative_name'] ?? '-'); ?></td>
                <td><span class="badge <?php echo $cust_item['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>"><?php echo $cust_item['status'] == 'active' ? 'نشط' : 'غير نشط'; ?></span></td>
                <td>
                    <?php if ($cust_item['is_main_account']): ?>
                        <a href="index.php?page=customers&action=view_branches&id=<?php echo $cust_item['customer_id']; ?>"><?php echo $cust_item['branch_count']; ?> فرع</a>
                    <?php else: echo '-'; endif; ?>
                </td>
                <td class="actions-cell">
                    <a href="index.php?page=customers&action=edit&id=<?php echo $cust_item['customer_id']; ?>" class="button-link edit-btn">تعديل</a>
                    <form action="actions/customer_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                        <input type="hidden" name="customer_id" value="<?php echo $cust_item['customer_id']; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link delete-btn">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?>
                <tr><td colspan="8">لا يوجد عملاء لعرضهم. <?php echo !empty($search_term) ? 'لم يتم العثور على نتائج للبحث "' . htmlspecialchars($search_term) . '".' : ''; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =====| بداية أزرار الترقيم |===== -->
<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <div class="page-info">
            صفحة <?php echo $page_num; ?> من <?php echo $total_pages; ?> (إجمالي <?php echo $total_items; ?> عميل)
        </div>
        <div class="page-links">
            <?php if ($page_num > 1): ?>
                <a href="index.php?page=customers&p=1&search=<?php echo urlencode($search_term); ?>">«</a>
                <a href="index.php?page=customers&p=<?php echo $page_num - 1; ?>&search=<?php echo urlencode($search_term); ?>">‹</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);
            
            if ($start > 1) echo "<span>...</span>";

            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="index.php?page=customers&p=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" class="<?php echo ($i == $page_num) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($end < $total_pages) echo "<span>...</span>"; ?>

            <?php if ($page_num < $total_pages): ?>
                <a href="index.php?page=customers&p=<?php echo $page_num + 1; ?>&search=<?php echo urlencode($search_term); ?>">›</a>
                <a href="index.php?page=customers&p=<?php echo $total_pages; ?>&search=<?php echo urlencode($search_term); ?>">»</a>
            <?php endif; ?>
        </div>
    <?php elseif($total_items > 0): ?>
        <div class="page-info">
             إجمالي <?php echo $total_items; ?> عميل
        </div>
    <?php endif; ?>
</div>
<!-- =====| نهاية أزرار الترقيم |===== -->

<!-- =====| تنسيقات CSS إضافية |===== -->
<style>
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.toolbar .actions-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.search-form form {
    display: flex;
    gap: 5px;
}
.search-input {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-width: 250px;
}
.search-btn {
    padding: 8px 15px;
}
.table-container {
    overflow-x: auto;
}
.main-account-row {
    background-color: #e9f5ff;
    font-weight: bold;
}
.inactive-row {
    opacity: 0.6;
    text-decoration: line-through;
}
.badge { padding: 3px 8px; border-radius: 10px; color: white; font-size: 0.8em; }
.status-active { background-color: var(--success-color); }
.status-inactive { background-color: var(--secondary-color); }
.badge-main { font-size: 0.8em; color: var(--primary-color); font-weight: normal; }
.actions-cell .button-link, .actions-cell form {
    margin-bottom: 3px;
}
.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    flex-wrap: wrap;
    gap: 10px;
}
.page-info {
    color: #555;
    font-size: 0.9em;
}
.page-links {
    display: flex;
    align-items: center;
}
.page-links a, .page-links span {
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #ddd;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.2s;
}
.page-links a:hover {
    background-color: #e9ecef;
}
.page-links a.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    cursor: default;
}
.page-links span {
    border: none;
    color: #777;
    padding: 6px 0;
}
</style>