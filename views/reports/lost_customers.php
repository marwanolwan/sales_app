<?php // views/reports/lost_customers.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p class="text-muted">
    العملاء الذين قاموا بالشراء في الفترة من 
    <strong><?php echo htmlspecialchars($filter_previous_start); ?></strong> إلى <strong><?php echo htmlspecialchars($filter_previous_end); ?></strong>،
    ولم يقوموا بأي عملية شراء في الفترة من
    <strong><?php echo htmlspecialchars($filter_current_start); ?></strong> إلى <strong><?php echo htmlspecialchars($filter_current_end); ?></strong>.
</p>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>قائمة العملاء المفقودين (<?php echo count($report_data); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا يوجد عملاء مفقودون يطابقون الفلاتر المحددة.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم العميل</th>
                        <th>رمز العميل</th>
                        <th>المندوب المسؤول</th>
                        <th>آخر تاريخ شراء مسجل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($report_data as $index => $customer): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <a href="index.php?page=customers&action=edit&id=<?php echo $customer['customer_id']; ?>">
                                <?php echo htmlspecialchars($customer['customer_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><?php echo htmlspecialchars($customer['representative_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['last_purchase_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>