<?php // views/reports/customer_item_yoy_comparison.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج المقارنة بين سنة <?php echo htmlspecialchars($filter_year_primary); ?> وسنة <?php echo htmlspecialchars($filter_year_secondary); ?></h3>
    </div>
    <div class="card-body">
        <div class="toolbar" style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
            <div class="search-form">
                <form action="index.php" method="GET">
                    <!-- تمرير كل الفلاتر الحالية للحفاظ عليها عند البحث -->
                    <input type="hidden" name="page" value="reports">
                    <input type="hidden" name="type" value="customer_item_yoy_comparison">
                    <?php foreach ($_GET as $key => $value): if ($key !== 'search_customer' && $key !== 'search_product' && $key !== 'p'): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endif; endforeach; ?>

                    <input type="text" name="search_customer" placeholder="ابحث باسم الزبون..." value="<?php echo htmlspecialchars($search_term_customer); ?>" class="search-input">
                    <input type="text" name="search_product" placeholder="ابحث باسم الصنف..." value="<?php echo htmlspecialchars($search_term_product); ?>" class="search-input">
                    <button type="submit" class="button-link search-btn">بحث</button>
                    <?php if(!empty($search_term_customer) || !empty($search_term_product)): ?>
                        <?php
                        $clear_search_params = $_GET;
                        unset($clear_search_params['search_customer'], $clear_search_params['search_product'], $clear_search_params['p']);
                        ?>
                        <a href="index.php?<?php echo http_build_query($clear_search_params); ?>" class="button-link" style="background-color: #777;">مسح البحث</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد بيانات مبيعات تطابق الفلاتر المحددة للمقارنة.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>الزبون</th>
                        <th>الصنف</th>
                        <th>مبيعات <?php echo $filter_year_primary; ?> (كمية)</th>
                        <th>مبيعات <?php echo $filter_year_secondary; ?> (كمية)</th>
                        <th>الفرق (كمية)</th>
                        <th>نسبة النمو (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total_primary = 0;
                    $grand_total_secondary = 0;
                    foreach($report_data as $item): 
                        $sales1 = $item['sales_primary'];
                        $sales2 = $item['sales_secondary'];
                        $difference = $sales1 - $sales2;
                        $growth = ($sales2 > 0) ? ($difference / $sales2) * 100 : ($sales1 > 0 ? 100.0 : 0.0);
                        $grand_total_primary += $sales1;
                        $grand_total_secondary += $sales2;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format($sales1, 2); ?></td>
                        <td><?php echo number_format($sales2, 2); ?></td>
                        <td style="font-weight: bold; color: <?php echo $difference >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                            <?php echo number_format($difference, 2); ?>
                        </td>
                        <td style="font-weight: bold; color: <?php echo $growth >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;">
                            <?php echo number_format($growth, 2); ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="2">الإجمالي العام</td>
                        <td><?php echo number_format($grand_total_primary, 2); ?></td>
                        <td><?php echo number_format($grand_total_secondary, 2); ?></td>
                        <?php
                            $grand_difference = $grand_total_primary - $grand_total_secondary;
                            $grand_growth = ($grand_total_secondary > 0) ? ($grand_difference / $grand_total_secondary) * 100 : ($grand_total_primary > 0 ? 100.0 : 0.0);
                        ?>
                        <td style="color: <?php echo $grand_difference >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;"><?php echo number_format($grand_difference, 2); ?></td>
                        <td style="color: <?php echo $grand_growth >= 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>;"><?php echo number_format($grand_growth, 2); ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
          <?php
        // تمرير الفلاتر إلى ملف الترقيم
        $pagination_params = [
            'search_customer' => $search_term_customer,
            'search_product' => $search_term_product,
            // بقية الفلاتر تأتي من _filters.php
            'region_id' => $filter_region_id,
            'representative_id' => $filter_rep_id,
            'customer_id' => $filter_customer_id,
            'product_id' => $filter_product_id
        ];
        include __DIR__ . '/../partials/pagination.php';
        ?>
        <?php endif; ?>
    </div>
</div>
<style>
/* يمكنك نقل هذه الأنماط إلى ملف style.css الرئيسي لتجنب التكرار */
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
.table-container {
    overflow-x: auto;
}
.product-thumbnail {
    max-width: 60px;
    max-height: 60px;
    border: 1px solid #ddd;
    border-radius: 4px;
    object-fit: cover;
}
.no-image {
    display: inline-block;
    width: 60px;
    height: 60px;
    line-height: 60px;
    text-align: center;
    background-color: #f0f0f0;
    color: #aaa;
    border-radius: 4px;
    font-size: 0.8em;
}
.inactive-row {
    opacity: 0.6;
    text-decoration: line-through;
}
.badge { padding: 3px 8px; border-radius: 10px; color: white; font-size: 0.8em; }
.status-active { background-color: var(--success-color); }
.status-inactive { background-color: var(--secondary-color); }
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