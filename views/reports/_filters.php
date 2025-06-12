<?php // views/reports/_filters.php ?>

<div class="filter-bar card">
    <div class="card-body">
        <form action="index.php" method="GET" id="reportsFilterForm">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($report_type); ?>">
            
            <!-- ======================= قسم الفلاتر القياسية ======================= -->
            <div id="standard-filters-wrapper">
                <div class="form-row">
                    <div class="form-group">
                        <label>السنة:</label>
                        <select name="year" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($filter_year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الفترة:</label>
                        <select name="period_type" id="period_type_filter" onchange="this.form.submit()">
                            <option value="annually" <?php echo ($filter_period_type == 'annually') ? 'selected' : ''; ?>>سنوي</option>
                            <option value="quarterly" <?php echo ($filter_period_type == 'quarterly') ? 'selected' : ''; ?>>ربع سنوي</option>
                            <option value="monthly" <?php echo ($filter_period_type == 'monthly') ? 'selected' : ''; ?>>شهري</option>
                        </select>
                    </div>
                    <div class="form-group period-options" id="month_filter" style="display:none;">
                        <label>الشهر:</label>
                        <select name="month" onchange="this.form.submit()">
                            <?php 
                                $arabic_months = [1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل', 5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس', 9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر'];
                                foreach($arabic_months as $num => $name): 
                            ?>
                                <option value="<?php echo $num; ?>" <?php echo ($filter_month == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="form-group period-options" id="quarter_filter" style="display:none;">
                        <label>الربع:</label>
                        <select name="quarter" onchange="this.form.submit()">
                            <option value="1" <?php echo ($filter_quarter == 1) ? 'selected' : ''; ?>>الربع الأول</option>
                            <option value="2" <?php echo ($filter_quarter == 2) ? 'selected' : ''; ?>>الربع الثاني</option>
                            <option value="3" <?php echo ($filter_quarter == 3) ? 'selected' : ''; ?>>الربع الثالث</option>
                            <option value="4" <?php echo ($filter_quarter == 4) ? 'selected' : ''; ?>>الربع الرابع</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ======================= قسم فلاتر المقارنة السنوية ======================= -->
            <div id="yoy_comparison_filters" style="display:none;">
                 <div class="form-row">
                    <div class="form-group">
                        <label>السنة الأساسية:</label>
                        <select name="year_primary" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo (isset($filter_year_primary) && $filter_year_primary == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                     <div class="form-group">
                        <label>سنة المقارنة:</label>
                        <select name="year_secondary" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo (isset($filter_year_secondary) && $filter_year_secondary == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- ======================= قسم فلاتر العملاء المفقودين ======================= -->
            <div id="date_range_filters" style="display:none;">
                <div class="form-row">
                    <div class="form-group">
                        <label>الفترة الحالية (لم يشتروا فيها):</label>
                        <div class="date-range-group">
                            <input type="date" name="current_start" value="<?php echo htmlspecialchars($filter_current_start ?? ''); ?>">
                            <span>إلى</span>
                            <input type="date" name="current_end" value="<?php echo htmlspecialchars($filter_current_end ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>الفترة السابقة (اشتروا فيها):</label>
                        <div class="date-range-group">
                            <input type="date" name="previous_start" value="<?php echo htmlspecialchars($filter_previous_start ?? ''); ?>">
                            <span>إلى</span>
                            <input type="date" name="previous_end" value="<?php echo htmlspecialchars($filter_previous_end ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ======================= قسم الفلاتر المشتركة والاختيارية ======================= -->
            <div class="form-row">
                 <div class="form-group">
                    <label>المنطقة:</label>
                    <select name="region_id" onchange="this.form.submit()">
                        <option value="all">كل المناطق</option>
                        <?php foreach($regions as $region): ?>
                            <option value="<?php echo $region['region_id']; ?>" <?php echo ($filter_region_id == $region['region_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($region['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>المندوب:</label>
                    <select name="representative_id" onchange="this.form.submit()">
                        <option value="all">كل المندوبين <?php echo ($filter_region_id !== 'all') ? '(في المنطقة المحددة)' : ''; ?></option>
                        <?php foreach($representatives as $rep): ?>
                             <option value="<?php echo $rep['user_id']; ?>" <?php echo ($filter_rep_id == $rep['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rep['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group optional-filter" id="family_filter_group">
                    <label>عائلة المنتج:</label>
                    <select name="family_id" onchange="this.form.submit()">
                        <option value="all">كل العائلات</option>
                        <?php foreach($product_families as $family): ?>
                             <option value="<?php echo $family['family_id']; ?>" <?php echo (isset($filter_family_id) && $filter_family_id == $family['family_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($family['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group optional-filter" id="customer_filter_group">
                    <label>الزبون:</label>
                    <select name="customer_id" onchange="this.form.submit()">
                        <option value="all">كل الزبائن</option>
                        <?php if(isset($all_customers)) foreach($all_customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>" <?php echo (isset($filter_customer_id) && $filter_customer_id == $customer['customer_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="form-group optional-filter" id="product_filter_group">
                    <label>الصنف:</label>
                    <select name="product_id" onchange="this.form.submit()">
                        <option value="all">كل الأصناف</option>
                         <?php if(isset($all_products)) foreach($all_products as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>" <?php echo (isset($filter_product_id) && $filter_product_id == $product['product_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($product['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group optional-filter" id="limit_filter_group">
                    <label>عدد النتائج:</label>
                    <select name="limit" onchange="this.form.submit()">
                        <option value="10" <?php echo ($filter_limit == 10) ? 'selected' : ''; ?>>أعلى 10</option>
                        <option value="20" <?php echo ($filter_limit == 20) ? 'selected' : ''; ?>>أعلى 20</option>
                        <option value="50" <?php echo ($filter_limit == 50) ? 'selected' : ''; ?>>أعلى 50</option>
                    </select>
                </div>
            </div>
            <div class="form-row" style="justify-content: flex-end;">
                 <div class="form-group">
                    <a href="index.php?page=reports&type=<?php echo htmlspecialchars($report_type); ?>" class="button-link" style="background-color: #777;">إلغاء الفلاتر</a>
                    <button type="submit" class="button-link">تحديث</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportType = '<?php echo $report_type; ?>';
    
    // تعريف كل أقسام الفلاتر
    const standardFilters = document.getElementById('standard-filters-wrapper');
    const yoyComparisonFilters = document.getElementById('yoy_comparison_filters');
    const dateRangeFilters = document.getElementById('date_range_filters');
    const periodFilter = document.getElementById('period_type_filter');
    const monthFilter = document.getElementById('month_filter');
    const quarterFilter = document.getElementById('quarter_filter');

    // مصفوفة لتحديد الفلاتر الاختيارية التي يجب إظهارها لكل تقرير
    const reportOptionalFilters = {
        'top_selling_items': ['limit_filter_group'],
        'stagnant_items': ['family_filter_group'],
        'customer_item_yoy_comparison': ['customer_filter_group', 'product_filter_group'],
        'sales_mix': ['family_filter_group'],
        // أضف التقارير الأخرى هنا
    };
    
    // إخفاء جميع الأقسام والفلاتر الاختيارية في البداية
    if (standardFilters) standardFilters.style.display = 'none';
    if (yoyComparisonFilters) yoyComparisonFilters.style.display = 'none';
    if (dateRangeFilters) dateRangeFilters.style.display = 'none';
    document.querySelectorAll('.optional-filter').forEach(el => el.style.display = 'none');

    // إظهار الأقسام والفلاتر بناءً على نوع التقرير
    if (reportType === 'lost_customers') {
        dateRangeFilters.style.display = 'block';
    } else if (reportType === 'customer_item_yoy_comparison') {
        yoyComparisonFilters.style.display = 'block';
        standardFilters.style.display = 'block'; // أظهر فلاتر الفترة الزمنية أيضًا
    } else {
        standardFilters.style.display = 'block';
    }
    
    const visibleOptionalFilters = reportOptionalFilters[reportType] || [];
    visibleOptionalFilters.forEach(filterId => {
        const el = document.getElementById(filterId);
        if (el) el.style.display = 'flex';
    });

    // التحكم في إظهار فلتر الشهر/الربع
    if (periodFilter) {
        if (monthFilter) monthFilter.style.display = (periodFilter.value === 'monthly') ? 'flex' : 'none';
        if (quarterFilter) quarterFilter.style.display = (periodFilter.value === 'quarterly') ? 'flex' : 'none';
    }
});
</script>

<style>
/* يمكنك نقل هذا إلى style.css */
.filter-bar { padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px; }
.filter-bar form { display: flex; flex-direction: column; gap: 15px; }
.filter-bar .form-row { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; }
.filter-group label { margin-bottom: 5px; font-size: 0.9em; font-weight: bold; color: #555; }
.filter-group select, .filter-group input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 180px; }
.date-range-group { display: flex; align-items: center; gap: 10px; }
</style>