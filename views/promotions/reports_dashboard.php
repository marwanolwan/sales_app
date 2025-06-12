<?php // views/promotions/reports_dashboard.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<!-- ======================= التقرير العام ======================= -->
<div class="card">
    <div class="card-header">
        <h3>تقرير عام بالزبائن أصحاب الاتفاقيات</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>اسم الزبون</th>
                        <th>الرمز</th>
                        <th>حملات سنوية</th>
                        <th>حملات مؤقتة</th>
                        <th>عقود سنوية</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers_with_promos as $customer): ?>
                    <tr>
                        <td>
                            <a href="index.php?page=promotion_reports&action=customer_report&customer_id=<?php echo $customer['customer_id']; ?>">
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><?php echo $customer['annual_count'] > 0 ? $customer['annual_count'] : '-'; ?></td>
                        <td><?php echo $customer['temp_count'] > 0 ? $customer['temp_count'] : '-'; ?></td>
                        <td><?php echo $customer['contract_count'] > 0 ? $customer['contract_count'] : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers_with_promos)): ?>
                    <tr><td colspan="5">لا يوجد زبائن لديهم اتفاقيات حاليًا.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================= فلاتر التقرير المخصص ======================= -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>عرض تقرير مفصل لزبون</h3>
    </div>
    <div class="card-body">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="promotion_reports">
            <input type="hidden" name="action" value="customer_report">
            <div class="form-group">
                <label for="customer_id">اختر الزبون:</label>
                <select name="customer_id" id="customer_id" required>
                    <option value="">-- اختر زبونًا --</option>
                    <?php foreach ($all_customers as $customer): ?>
                        <option value="<?php echo $customer['customer_id']; ?>">
                            <?php echo htmlspecialchars($customer['name']) . ' (' . htmlspecialchars($customer['customer_code']) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="button-link">عرض التقرير</button>
            </div>
        </form>
    </div>
</div>