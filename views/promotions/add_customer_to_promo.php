<?php // views/promotions/add_customer_to_promo.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <div class="actions">
            <a href="index.php?page=promotions" class="button-link" style="background-color:#6c757d;">
                <i class="fas fa-arrow-left"></i> العودة لقائمة الزبائن
            </a>
        </div>
    </div>
    <div class="card-body">
        <p>اختر زبونًا من القائمة أدناه لبدء إضافة اتفاقيات وعقود له.</p>
        
        <div class="toolbar" style="justify-content: flex-end;">
            <div class="search-form">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="promotions">
                    <input type="hidden" name="action" value="add_customer">
                    <input type="text" name="search" placeholder="ابحث بالاسم أو الرمز..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                    <button type="submit" class="button-link search-btn">بحث</button>
                    <?php if(!empty($search_term)): ?>
                        <a href="index.php?page=promotions&action=add_customer" class="button-link" style="background-color: #777;">مسح</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>اسم الزبون</th>
                        <th>الرمز</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers_to_add as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td>
                            <a href="index.php?page=promotions&customer_id=<?php echo $customer['customer_id']; ?>" class="button-link add-btn">
                                اختر وابدأ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers_to_add)): ?>
                    <tr><td colspan="3">لا يوجد زبائن جدد لإضافتهم (قد يكون لجميع الزبائن اتفاقيات بالفعل).</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        // إعادة استخدام نفس كود الترقيم
        include __DIR__ . '/../partials/pagination.php';
        ?>
    </div>
</div>