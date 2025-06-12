<?php // views/products/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="toolbar">
    <div class="actions-bar">
        <a href="index.php?page=products&action=add" class="button-link add-btn">إضافة منتج جديد</a>
        <a href="index.php?page=products&action=import" class="button-link" style="background-color:#fd7e14;">استيراد منتجات من Excel</a>
    </div>

    <!-- =====| بداية نموذج البحث والفلترة |===== -->
    <div class="search-form">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="products">
            
            <div class="filter-group">
                <label for="filter_new">عرض:</label>
                <select name="filter_new" id="filter_new" class="form-control">
                    <option value="all" <?php echo ($filter_new == 'all') ? 'selected' : ''; ?>>الكل</option>
                    <option value="new_only" <?php echo ($filter_new == 'new_only') ? 'selected' : ''; ?>>المنتجات الجديدة فقط</option>
                </select>
            </div>
            
            <input type="text" name="search" placeholder="ابحث بالاسم, الرمز, العائلة..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
            
            <button type="submit" class="button-link search-btn">بحث</button>
            
            <?php if(!empty($search_term) || $filter_new !== 'all'): ?>
                <a href="index.php?page=products" class="button-link" style="background-color: #777;">مسح الفلتر</a>
            <?php endif; ?>
        </form>
    </div>
    <!-- =====| نهاية نموذج البحث والفلترة |===== -->
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>الصورة</th>
                <th>رمز المنتج</th>
                <th>اسم المنتج</th>
                <th>عائلة المنتج</th>
                <th>الوحدة</th>
                <th>التعبئة</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products_list_display as $prod_item): ?>
                <?php
                    // تحديد ما إذا كان المنتج جديدًا حاليًا
                    $is_currently_new = $prod_item['is_new_product'] && 
                                        (is_null($prod_item['new_product_end_date']) || $prod_item['new_product_end_date'] >= date('Y-m-d'));
                ?>
                <tr class="<?php echo !$prod_item['is_active'] ? 'inactive-row' : ''; ?>">
                    <td>
                        <?php if (!empty($prod_item['product_image_path']) && file_exists(PRODUCTS_IMAGE_DIR . $prod_item['product_image_path'])): ?>
                            <img src="<?php echo PRODUCTS_IMAGE_DIR . htmlspecialchars($prod_item['product_image_path']); ?>" alt="<?php echo htmlspecialchars($prod_item['name']);?>" class="product-thumbnail">
                        <?php else: ?> 
                            <span class="no-image">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($prod_item['product_code']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($prod_item['name']); ?>
                        <?php if ($is_currently_new): ?>
                            <span class="new-product-badge" title="منتج جديد حتى <?php echo htmlspecialchars($prod_item['new_product_end_date'] ?? 'أجل غير مسمى'); ?>">جديد ✨</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($prod_item['family_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($prod_item['unit']); ?></td>
                    <td><?php echo htmlspecialchars($prod_item['packaging_details'] ?? '-'); ?></td>
                    <td><span class="badge <?php echo $prod_item['is_active'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $prod_item['is_active'] ? 'فعال' : 'غير فعال'; ?></span></td>
                    <td class="actions-cell">
                        <a href="index.php?page=products&action=edit&id=<?php echo $prod_item['product_id']; ?>" class="button-link edit-btn">تعديل</a>
                        <form action="actions/product_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد؟');">
                            <input type="hidden" name="product_id" value="<?php echo $prod_item['product_id']; ?>">
                            <?php csrf_input(); ?>
                            <button type="submit" class="button-link delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products_list_display)): ?>
                <tr><td colspan="8">لا توجد منتجات لعرضها. <?php echo !empty($search_term) || $filter_new !== 'all' ? 'لم يتم العثور على نتائج تطابق الفلاتر الحالية.' : ''; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =====| بداية أزرار الترقيم |===== -->
<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <div class="page-info">
            صفحة <?php echo $page_num; ?> من <?php echo $total_pages; ?> (إجمالي <?php echo $total_items; ?> منتج)
        </div>
        <div class="page-links">
            <?php 
                // الحفاظ على الفلاتر عند التنقل
                $query_params = "page=products&search=" . urlencode($search_term) . "&filter_new=" . urlencode($filter_new);
            ?>
            <?php if ($page_num > 1): ?>
                <a href="index.php?<?php echo $query_params; ?>&p=1">«</a>
                <a href="index.php?<?php echo $query_params; ?>&p=<?php echo $page_num - 1; ?>">‹</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);
            if ($start > 1) echo "<span>...</span>";
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="index.php?<?php echo $query_params; ?>&p=<?php echo $i; ?>" class="<?php echo ($i == $page_num) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($end < $total_pages) echo "<span>...</span>"; ?>

            <?php if ($page_num < $total_pages): ?>
                <a href="index.php?<?php echo $query_params; ?>&p=<?php echo $page_num + 1; ?>">›</a>
                <a href="index.php?page=products&<?php echo $query_params; ?>&p=<?php echo $total_pages; ?>">»</a>
            <?php endif; ?>
        </div>
    <?php elseif($total_items > 0): ?>
        <div class="page-info">
             إجمالي <?php echo $total_items; ?> منتج
        </div>
    <?php endif; ?>
</div>
<!-- =====| نهاية أزرار الترقيم |===== -->

<style>
/* يمكنك نقل هذه الأنماط إلى ملف style.css الرئيسي */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
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
    gap: 10px; /* زيادة المسافة بين عناصر الفلتر */
    align-items: flex-end; /* محاذاة العناصر للأسفل */
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
.search-input, .filter-group select {
    padding: 15px 18px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.search-input { min-width: 220px; }
.table-container { overflow-x: auto; }
.product-thumbnail { max-width: 60px; max-height: 60px; border: 1px solid #ddd; border-radius: 4px; object-fit: cover; }
.no-image { display: inline-block; width: 60px; height: 60px; line-height: 60px; text-align: center; background-color: #f0f0f0; color: #aaa; border-radius: 4px; font-size: 0.8em; }
.inactive-row { opacity: 0.6; }
.badge { padding: 3px 8px; border-radius: 10px; color: white; font-size: 0.8em; }
.status-active { background-color: var(--success-color); }
.status-inactive { background-color: var(--secondary-color); }
.new-product-badge {
    background-color: #17a2b8; /* لون مميز للمنتجات الجديدة */
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.75em;
    font-weight: bold;
    margin-right: 5px;
    cursor: help;
    vertical-align: middle;
}
.actions-cell .button-link, .actions-cell form { margin-bottom: 3px; }
.pagination { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: #f8f9fa; border-radius: 5px; flex-wrap: wrap; gap: 10px; }
.page-info { color: #555; font-size: 0.9em; }
.page-links { display: flex; align-items: center; }
.page-links a, .page-links span { padding: 6px 12px; margin: 0 2px; border: 1px solid #ddd; color: #007bff; text-decoration: none; border-radius: 4px; transition: background-color 0.2s; }
.page-links a:hover { background-color: #e9ecef; }
.page-links a.active { background-color: #007bff; color: white; border-color: #007bff; cursor: default; }
.page-links span { border: none; color: #777; padding: 6px 0; }
</style>