<?php // views/reports/customer_purchase_list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; // تضمين الفلاتر المشتركة ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>قائمة نقاط البيع</h3>
    </div>
      <!-- =====| بداية التعديل: إضافة نموذج البحث |===== -->
        <div class="toolbar" style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
            <div class="search-form">
                <form action="index.php" method="GET">
                    <!-- تمرير كل الفلاتر الحالية للحفاظ عليها عند البحث -->
                    <input type="hidden" name="page" value="reports">
                    <input type="hidden" name="type" value="customer_purchase_analysis">
                    <input type="hidden" name="year" value="<?php echo htmlspecialchars($filter_year); ?>">
                    <input type="hidden" name="period_type" value="<?php echo htmlspecialchars($filter_period_type); ?>">
                    <input type="hidden" name="month" value="<?php echo htmlspecialchars($filter_month); ?>">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($filter_quarter); ?>">
                    <input type="hidden" name="region_id" value="<?php echo htmlspecialchars($filter_region_id); ?>">
                    <input type="hidden" name="representative_id" value="<?php echo htmlspecialchars($filter_rep_id); ?>">

                    <input type="text" name="search" placeholder="ابحث بالاسم أو الرمز..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                    <button type="submit" class="button-link search-btn">بحث</button>
                    <?php if(!empty($search_term)): ?>
                        <?php
                        // رابط لمسح البحث فقط مع الاحتفاظ بباقي الفلاتر
                        $clear_search_params = $_GET;
                        unset($clear_search_params['search']);
                        unset($clear_search_params['p']); // العودة للصفحة الأولى
                        ?>
                        <a href="index.php?<?php echo http_build_query($clear_search_params); ?>" class="button-link" style="background-color: #777;">مسح البحث</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <!-- =====| نهاية التعديل |===== -->
        
         <p class="info-message">اختر نقطة بيع من القائمة أدناه لعرض تحليل مفصل لمشترياتها خلال الفترة المحددة.</p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم نقطة البيع</th>
                        <th>الرمز</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_number = ($page_num - 1) * $items_per_page;
                    foreach($report_data as $customer): 
                        $row_number++;
                        // بناء رابط التفاصيل مع الاحتفاظ بكل الفلاتر الحالية
                        $details_params = $_GET;
                        $details_params['customer_id'] = $customer['customer_id'];
                        $details_url = 'index.php?' . http_build_query($details_params);
                    ?>
                    <tr>
                        <td><?php echo $row_number; ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><a href="<?php echo $details_url; ?>" class="button-link">عرض التفاصيل</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="4">لا يوجد عملاء يطابقون الفلاتر أو البحث المحدد.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        // تمرير الفلاتر إلى ملف الترقيم
        $pagination_params = [
            'search' => $search_term,
            // بقية الفلاتر تأتي من _filters.php
            'region_id' => $filter_region_id,
            'representative_id' => $filter_rep_id
        ];
        include __DIR__ . '/../partials/pagination.php';
        ?>
    </div>
</div>
</div>