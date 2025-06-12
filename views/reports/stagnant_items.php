<?php // views/reports/stagnant_items.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير (الأصناف التي لم يتم بيعها)</h3>
        <span class="badge-count">عدد الأصناف: <?php echo count($report_data); ?></span>
    </div>
    <div class="card-body">
         <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد أصناف راكدة تطابق الفلاتر المحددة. هذا يعني أن جميع الأصناف (ضمن الفلاتر) قد تم بيعها.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رمز الصنف</th>
                        <th>اسم الصنف</th>
                        <th>عائلة المنتج</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($report_data as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($item['product_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['family_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
          <?php
        // تمرير الفلاتر الحالية إلى ملف الترقيم
      
        include __DIR__ . '/../partials/pagination.php';
        ?>
        <!-- =====| نهاية التعديل |===== -->

        <?php endif; ?>
    </div>
</div>


<style>
/* يمكنك نقل هذا إلى style.css */
.card-header .badge-count {
    background-color: var(--secondary-color);
    color: white;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.9em;
}
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