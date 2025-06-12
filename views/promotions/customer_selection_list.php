<?php // views/promotions/customer_selection_list.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <div class="actions">
            <a href="index.php?page=promotion_reports" class="button-link" style="background-color: var(--secondary-color);">
                <i class="fas fa-chart-bar"></i> عرض التقارير العامة
            </a>
            <a href="index.php?page=promotions&action=add_customer" class="button-link add-btn">
                <i class="fas fa-plus"></i> إضافة زبون جديد للاتفاقيات
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="filter-bar">
            <form action="index.php" method="GET" id="promoCustomerFilterForm">
                <input type="hidden" name="page" value="promotions">
                
                <div class="filter-group">
                    <label for="region_id">المنطقة:</label>
                    <select name="region_id" id="region_id" onchange="this.form.submit()">
                        <option value="all">كل المناطق</option>
                        <?php foreach ($regions as $region): ?>
                            <option value="<?php echo $region['region_id']; ?>" <?php echo ($filter_region_id == $region['region_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($region['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="supervisor_id">المشرف:</label>
                    <select name="supervisor_id" id="supervisor_id" onchange="this.form.submit()">
                        <option value="all">كل المشرفين</option>
                         <?php foreach ($supervisors as $supervisor): ?>
                            <option value="<?php echo $supervisor['user_id']; ?>" <?php echo ($filter_supervisor_id == $supervisor['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supervisor['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="representative_id">المندوب:</label>
                    <select name="representative_id" id="representative_id">
                        <option value="all">كل المندوبين</option>
                        <?php foreach ($representatives as $rep): ?>
                            <option value="<?php echo $rep['user_id']; ?>" <?php echo ($filter_rep_id == $rep['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rep['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="promoter_id">المروج:</label>
                    <select name="promoter_id" id="promoter_id">
                        <option value="all">كل المروجين</option>
                        <?php foreach ($promoters as $promo): ?>
                            <option value="<?php echo $promo['user_id']; ?>" <?php echo ($filter_promoter_id == $promo['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($promo['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group search-group">
                    <input type="text" name="search" placeholder="ابحث بالاسم أو الرمز..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                    <button type="submit" class="button-link search-btn">تطبيق</button>
                    <a href="index.php?page=promotions" class="button-link" style="background-color: #777;">إلغاء الفلاتر</a>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr><th>اسم الزبون</th><th>الرمز</th><th>إجراءات</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($customers_with_promos as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><a href="index.php?page=promotions&customer_id=<?php echo $customer['customer_id']; ?>" class="button-link">إدارة الخدمات</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers_with_promos)): ?>
                    <tr><td colspan="3">لا يوجد زبائن يطابقون الفلاتر الحالية.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $pagination_params = [
            'region_id' => $filter_region_id,
            'supervisor_id' => $filter_supervisor_id,
            'representative_id' => $filter_rep_id,
            'promoter_id' => $filter_promoter_id,
            'search' => $search_term
        ];
        include __DIR__ . '/../partials/pagination.php';
        ?>
    </div>
</div>
<style>
/* يمكنك نقل هذه الأنماط إلى style.css */
.filter-bar {
    padding: 15px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 20px;
}
.filter-bar form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 20px;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    margin-bottom: 5px;
    font-size: 0.9em;
    font-weight: bold;
}
.filter-group select, .filter-group input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.filter-group.search-group {
    flex-direction: row;
    align-items: flex-end;
    gap: 5px;
    margin-right: auto; /* يدفع مجموعة البحث إلى اليمين */
}
</style>