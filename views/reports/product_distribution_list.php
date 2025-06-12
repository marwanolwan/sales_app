<?php // views/reports/product_distribution_list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>قائمة الأصناف (مرتبة حسب الكمية المباعة)</h3>
    </div>
    <div class="card-body">
         <div class="toolbar" style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
            <div class="search-form">
                <form action="index.php" method="GET">
                    <!-- تمرير كل الفلاتر الحالية للحفاظ عليها عند البحث -->
                    <input type="hidden" name="page" value="reports">
                    <input type="hidden" name="type" value="product_distribution">
                    <input type="hidden" name="year" value="<?php echo htmlspecialchars($filter_year); ?>">
                    <input type="hidden" name="period_type" value="<?php echo htmlspecialchars($filter_period_type); ?>">
                    <input type="hidden" name="month" value="<?php echo htmlspecialchars($filter_month); ?>">
                    <input type="hidden" name="quarter" value="<?php echo htmlspecialchars($filter_quarter); ?>">
                    <input type="hidden" name="region_id" value="<?php echo htmlspecialchars($filter_region_id); ?>">
                    <input type="hidden" name="representative_id" value="<?php echo htmlspecialchars($filter_rep_id); ?>">

                    <input type="text" name="search" placeholder="ابحث بالاسم أو رمز الصنف..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                    <button type="submit" class="button-link search-btn">بحث</button>
                    <?php if(!empty($search_term)): ?>
                        <?php
                        $clear_search_params = $_GET;
                        unset($clear_search_params['search'], $clear_search_params['p']);
                        ?>
                        <a href="index.php?<?php echo http_build_query($clear_search_params); ?>" class="button-link" style="background-color: #777;">مسح البحث</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <p class="info-message">اختر صنفًا من القائمة لعرض تقرير التوزيع الخاص به (من اشترى ومن لم يشترِ).</p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الصنف</th>
                        <th>الرمز</th>
                        <th>الكمية المباعة (في الفترة المحددة)</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_number = ($page_num - 1) * $items_per_page;
                    foreach($report_data as $product): 
                        $row_number++;
                        $details_params = $_GET;
                        $details_params['product_id'] = $product['product_id'];
                        $details_url = 'index.php?' . http_build_query($details_params);
                    ?>
                    <tr>
                        <td><?php echo $row_number; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                        <td><?php echo number_format($product['total_quantity'], 2); ?></td>
                        <td><a href="<?php echo $details_url; ?>" class="button-link">عرض تقرير التوزيع</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($report_data)): ?>
                        <tr><td colspan="5">لا توجد منتجات تطابق الفلاتر المحددة.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
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