<?php // views/reports/sales_analysis_current_year.php ?>

<div class="analysis-section current-year-analysis">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>
    
    <form action="index.php?page=sales_analysis_current_year" method="GET" class="filter-form stylish-form">
        <input type="hidden" name="page" value="sales_analysis_current_year">
        <h4>فلاتر التحليل:</h4>
        <!-- ... كل حقول نموذج الفلاتر من الملف الأصلي ... -->
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="year_filter_input">السنة:</label>
                <select name="year" id="year_filter_input" class="form-control">
                    <?php for ($y_loop_item = date('Y'); $y_loop_item >= date('Y') - 5; $y_loop_item--): ?>
                        <option value="<?php echo $y_loop_item; ?>" <?php echo ($filter_year == $y_loop_item) ? 'selected' : ''; ?>><?php echo $y_loop_item; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <!-- ... بقية الفلاتر بنفس الطريقة ... -->
        </div>
        <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
    </form>

    <script>
        // ... كل كود الجافاسكريبت من الملف الأصلي ...
    </script>
    
    <?php if (isset($_SESSION['error_message_page'])): ?>
        <div class="message error-message"><?php echo $_SESSION['error_message_page']; unset($_SESSION['error_message_page']); ?></div>
    <?php endif; ?>

    <?php if (empty($current_year_analysis_results_arr) && $_SERVER['REQUEST_METHOD'] == 'GET' && count($_GET) > 1): ?>
        <div class="info-message">لا توجد بيانات تحليل لعرضها بناءً على الفلاتر الحالية.</div>
    <?php elseif (!empty($current_year_analysis_results_arr)): ?>
        <hr>
        <h3>نتائج التحليل لـ: <?php echo htmlspecialchars($overall_period_display_name_str); ?></h3>
        
        <!-- ... كل جداول العرض والرسوم البيانية من الملف الأصلي ... -->
        <div class="analysis-block">
            <h4>أداء المندوبين/المشرفين مقابل الأهداف</h4>
            <div style="overflow-x:auto;">
                <table>
                    <!-- ... محتوى الجدول ... -->
                </table>
            </div>
        </div>
        
        <div class="analysis-block">
            <h4>تحليل مساهمات المبيعات</h4>
            <div class="charts-container">
                <div class="chart-item">
                    <h5>مساهمة المندوبين</h5>
                    <canvas id="repContributionPieChartCanvas"></canvas>
                </div>
                <!-- ... بقية الرسوم ... -->
            </div>
            <script>
                // ... كل كود إنشاء الرسوم البيانية ...
            </script>
        </div>
    <?php endif; ?>
</div>

<style>
    /* ... كل كود CSS المضمن من الملف الأصلي ... */
</style>