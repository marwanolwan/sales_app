<?php
// views/item_sales/list.php
// واجهة عرض مبيعات الأصناف مع الفلاتر والترقيم والإجراءات المجمعة
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<!-- قسم الفلاتر -->
<div class="content-block filter-block">
    <h4>فلترة العرض</h4>
    <form action="index.php" method="GET" class="filter-form">
        <input type="hidden" name="page" value="item_sales">
        <div class="form-row">
            <div class="form-group col-md-2">
                <label for="filter_year">السنة:</label>
                <select id="filter_year" name="year" class="form-control">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="filter_month">الشهر:</label>
                <select id="filter_month" name="month" class="form-control">
                    <?php foreach ($months_map as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $filter_month == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($current_user_role !== 'representative'): ?>
            <div class="form-group col-md-2">
                <label for="filter_rep">المندوب:</label>
                <select id="filter_rep" name="representative_id" class="form-control select2-search">
                    <option value="all">الكل</option>
                    <?php foreach($representatives as $rep): ?>
                    <option value="<?php echo $rep['user_id']; ?>" <?php echo ($filter_rep == $rep['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rep['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group col-md-3">
                <label for="filter_product">المنتج:</label>
                <select id="filter_product" name="product_id" class="form-control select2-search">
                    <option value="all">الكل</option>
                    <?php foreach($products as $prod): ?>
                    <option value="<?php echo $prod['product_id']; ?>" <?php echo ($filter_product == $prod['product_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prod['name']) . ' (' . $prod['product_code'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="filter_customer">العميل:</label>
                <select id="filter_customer" name="customer_id" class="form-control select2-search">
                    <option value="all">الكل</option>
                    <?php foreach($customers as $cust): ?>
                    <option value="<?php echo $cust['customer_id']; ?>" <?php echo ($filter_customer == $cust['customer_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cust['name']) . ' (' . $cust['customer_code'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="button-link">تطبيق الفلاتر</button>
    </form>
</div>

<!-- قسم عرض المبيعات -->
<div class="content-block">
    <h4>مبيعات شهر <?php echo $months_map[$filter_month] . " " . $filter_year; ?> (عرض <?php echo count($item_sales); ?> من أصل <?php echo $total_records; ?> سجل)</h4>
    <?php if (empty($item_sales)): ?>
        <p class="info-message">لا توجد سجلات مبيعات تطابق الفلاتر الحالية.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>المندوب</th>
                        <th>العميل</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                        <th>معرف الدفعة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item_sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['representative_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['product_name']); ?> (<?php echo htmlspecialchars($sale['product_code']); ?>)</td>
                        <td><?php echo number_format($sale['quantity_sold'], 2); ?></td>
                        <td><?php echo $sale['unit_price'] !== null ? number_format($sale['unit_price'], 2) : '-'; ?></td>
                        <td><?php echo $sale['total_value'] !== null ? number_format($sale['total_value'], 2) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($sale['import_batch_id'] ?? 'يدوي'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- قسم الترقيم -->
        <div class="pagination">
            <?php if ($total_pages > 1): ?>
                <?php
                // بناء رابط الفلاتر للاحتفاظ به عند التنقل بين الصفحات
                $query_params = $_GET;
                unset($query_params['p']); // إزالة بارامتر الصفحة القديم
                $base_url = 'index.php?' . http_build_query($query_params);
                ?>

                <?php if ($current_page_num > 1): ?>
                    <a href="<?php echo $base_url . '&p=1'; ?>">« الأولى</a>
                    <a href="<?php echo $base_url . '&p=' . ($current_page_num - 1); ?>">‹ السابقة</a>
                <?php endif; ?>

                <?php for ($i = max(1, $current_page_num - 2); $i <= min($total_pages, $current_page_num + 2); $i++): ?>
                    <a href="<?php echo $base_url . '&p=' . $i; ?>" class="<?php echo ($i == $current_page_num) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($current_page_num < $total_pages): ?>
                    <a href="<?php echo $base_url . '&p=' . ($current_page_num + 1); ?>">التالية ›</a>
                    <a href="<?php echo $base_url . '&p=' . $total_pages; ?>">الأخيرة »</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<!-- قسم الإجراءات المجمعة -->
<div class="collapsible">
    <button class="collapsible-trigger">إجراءات مجمعة (استيراد / حذف)</button>
    <div class="collapsible-content">
        <!-- نموذج الاستيراد -->
        <div class="content-block" style="margin-top: 15px;">
            <h3>استيراد مبيعات الأصناف من ملف Excel</h3>
            <p>الأعمدة المطلوبة: السنة, الشهر, رمز العميل, رمز الصنف, اسم مستخدم المندوب, الكمية, (اختياري: السعر, الإجمالي).</p>
            <form action="actions/item_sales_import_preview.php" method="POST" enctype="multipart/form-data">
                <?php csrf_input(); ?>
                <div class="form-group">
                    <label for="item_sales_excel_file">اختر ملف Excel:</label>
                    <input type="file" id="item_sales_excel_file" name="item_sales_excel_file" accept=".xls,.xlsx" required>
                </div>
                <button type="submit" class="button-link">معاينة الاستيراد</button>
            </form>
        </div>

        <!-- نموذج الحذف -->
        <div class="content-block" style="border-top: 2px solid #dc3545; margin-top: 20px;">
            <h3 style="color: #721c24;">حذف مبيعات الأصناف لشهر كامل</h3>
            <form action="actions/item_sales_delete_monthly.php" method="POST" onsubmit="return confirm('هل أنت متأكد تمامًا؟ هذا الإجراء لا يمكن التراجع عنه.');">
                <?php csrf_input(); ?>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="delete_year_select">السنة:</label>
                        <select name="delete_year" id="delete_year_select" class="form-control" required>
                            <option value="">-- اختر --</option>
                            <?php for ($y_del = date('Y'); $y_del >= date('Y') - 5; $y_del--): ?>
                                <option value="<?php echo $y_del; ?>"><?php echo $y_del; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="delete_month_select">الشهر:</label>
                        <select name="delete_month" id="delete_month_select" class="form-control" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($months_map as $num_del => $name_del): ?>
                                <option value="<?php echo $num_del; ?>"><?php echo htmlspecialchars($name_del); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4" style="align-self: flex-end;">
                        <button type="submit" class="button-link delete-btn" style="width:100%;">حذف مبيعات الشهر</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى ملف style.css الرئيسي */
.filter-block { margin-bottom: 20px; }
.form-row { display: flex; flex-wrap: wrap; margin-right: -10px; margin-left: -10px; }
.form-group { padding-right: 10px; padding-left: 10px; margin-bottom: 1rem; }
.col-md-2 { flex: 0 0 16.66%; max-width: 16.66%; }
.col-md-3 { flex: 0 0 25%; max-width: 25%; }
.col-md-4 { flex: 0 0 33.33%; max-width: 33.33%; }
.table-responsive { overflow-x: auto; }
.pagination { margin-top: 20px; text-align: center; }
.pagination a { color: #007bff; padding: 8px 16px; text-decoration: none; border: 1px solid #ddd; margin: 0 2px; border-radius: 4px; transition: background-color .3s; }
.pagination a.active { background-color: #007bff; color: white; border: 1px solid #007bff; }
.pagination a:hover:not(.active) { background-color: #f2f2f2; }
.collapsible-trigger { background-color: #f1f1f1; color: #444; cursor: pointer; padding: 18px; width: 100%; border: none; text-align: right; outline: none; font-size: 15px; margin-top: 20px; border-radius: 5px; position: relative; }
.collapsible-trigger:after { content: '\002B'; font-size: 18px; font-weight: bold; color: #777; position: absolute; left: 18px; }
.collapsible-trigger.active:after { content: "\2212"; }
.collapsible-content { padding: 0 18px; display: none; overflow: hidden; background-color: #f1f1f1; border-radius: 0 0 5px 5px; border: 1px solid #ddd; border-top: none;}
</style>

<script>
// تفعيل Select2
$(document).ready(function() {
    $('.select2-search').select2({
        placeholder: "ابحث...",
        allowClear: true,
        dir: "rtl",
        width: '100%' // Ensures it fits the container
    });
});

// تفعيل الأكورديون (Collapsible)
var coll = document.getElementsByClassName("collapsible-trigger");
for (var i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        if (content.style.maxHeight){
            content.style.maxHeight = null;
        } else {
            // Set max-height to its scroll height for a smooth transition
            content.style.display = "block"; // Make it visible to calculate scrollHeight
            content.style.maxHeight = content.scrollHeight + "px";
        } 
    });
}
// إعادة تصميم الأكورديون ليكون أكثر سلاسة
const newAccordionStyle = document.createElement('style');
newAccordionStyle.innerHTML = `
    .collapsible-content {
        max-height: 0;
        transition: max-height 0.3s ease-out;
    }
`;
document.head.appendChild(newAccordionStyle);
</script>