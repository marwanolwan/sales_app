<?php // views/assets/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="toolbar">
    <div class="actions-bar">
        <a href="index.php?page=assets&action=add" class="button-link add-btn">إضافة أصل جديد</a>
        <a href="index.php?page=assets&action=types" class="button-link" style="background-color:#17a2b8;">إدارة أنواع الأصول</a>
    </div>

    <!-- =====| بداية قسم الفلاتر |===== -->
    <div class="search-form">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="assets">
            
            <div class="filter-group">
                <label for="filter_status">الحالة:</label>
                <select name="status" id="filter_status" class="form-control">
                    <option value="all">كل الحالات</option>
                    <?php foreach($statuses as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($filter_status == $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter_type">النوع:</label>
                <select name="type_id" id="filter_type" class="form-control">
                    <option value="all">كل الأنواع</option>
                    <?php foreach($asset_types_for_filter as $type): ?>
                        <option value="<?php echo $type['type_id']; ?>" <?php echo ($filter_type == $type['type_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['type_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="text" name="search" placeholder="ابحث بالرقم التسلسلي, العميل..." value="<?php echo htmlspecialchars($filter_search); ?>" class="search-input">
            
            <div class="filter-group">
                <button type="submit" class="button-link search-btn">بحث</button>
                <?php if ($filter_status !== 'all' || $filter_type !== 'all' || !empty($filter_search)): ?>
                    <a href="index.php?page=assets" class="button-link" style="background-color: #777;">مسح الفلتر</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>الرقم التسلسلي</th>
                <th>النوع</th>
                <th>الحالة</th>
                <th>الموقع الحالي (العميل)</th>
                <th>تاريخ الوضع</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assets_list)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px;">لا توجد أصول تطابق معايير البحث الحالية.</td></tr>
            <?php else: ?>
                <?php foreach($assets_list as $asset): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($asset['serial_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($asset['type_name']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $asset['status'])); ?>">
                            <?php echo htmlspecialchars($statuses[$asset['status']] ?? $asset['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($asset['status'] === 'With Customer' && !empty($asset['customer_name'])): ?>
                            <a href="index.php?page=customers&action=edit&id=<?php echo $asset['customer_id']; ?>">
                                <?php echo htmlspecialchars($asset['customer_name'] . ' (' . ($asset['customer_code'] ?? '') . ')'); ?>
                            </a>
                        <?php else: ?>
                            <span class="location-text"><?php echo htmlspecialchars($statuses[$asset['status']] ?? $asset['status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($asset['deployed_date'] ?? '---'); ?></td>
                    <td class="actions-cell">
                        <a href="index.php?page=assets&action=edit&id=<?php echo $asset['asset_id']; ?>" class="button-link edit-btn">
                            تعديل
                        </a>
                        <!-- =====| بداية الإضافة: نموذج الحذف |===== -->
                <form action="actions/asset_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الأصل بشكل نهائي؟ لا يمكن التراجع عن هذا الإجراء.');">
                    <input type="hidden" name="asset_id" value="<?php echo $asset['asset_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
                <!-- =====| نهاية الإضافة |===== -->
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.actions-bar {
    display: flex;
    gap: 10px;
}
.search-form {
    flex-grow: 1;
}
.search-form form {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.filter-group label {
    font-size: 0.9em;
    color: #555;
    margin-bottom: 0;
}
.search-input, .filter-group select.form-control {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-width: 160px;
}

/* Table Styles */
.table-container { overflow-x: auto; }
.actions-cell .button-link { padding: 5px 12px; font-size: 0.9em; }
.location-text { color: #6c757d; font-style: italic; }

/* Badge Styles */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    color: white;
    font-size: 0.8em;
    font-weight: 500;
    text-align: center;
    min-width: 90px;
}
.status-in-warehouse { background-color: #6c757d; }
.status-with-customer { background-color: #28a745; }
.status-under-maintenance { background-color: #ffc107; color: #212529; }
.status-retired { background-color: #343a40; }
</style>