<?php // views/pricing/archived_list.php ?>

<div class="toolbar">
    <div class="actions-bar">
        <a href="index.php?page=pricing" class="button-link">
            <i class="fa fa-arrow-left"></i> العودة للتسعيرات النشطة
        </a>
    </div>
    <!-- يمكن إضافة بحث في الأرشيف هنا لاحقًا -->
</div>

<h2>أرشيف التسعيرات</h2>

<?php if (empty($offers_by_product)): ?>
    <div class="info-message">لا توجد تسعيرات مؤرشفة حاليًا.</div>
<?php else: ?>
    <div class="pricing-list">
        <?php foreach ($offers_by_product as $product_id => $product_group): ?>
        <div class="product-pricing-card archived-card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-box-archive"></i> 
                    <?php echo htmlspecialchars($product_group['details']['product_name']); ?>
                    <span>(<?php echo htmlspecialchars($product_group['details']['product_code']); ?>)</span>
                </h3>
                <div class="header-actions">
                    <form action="actions/pricing_restore.php" method="POST" onsubmit="return confirm('هل تريد استعادة كل تسعيرات هذا الصنف؟');" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link-sm success-btn">استعادة الكل</button>
                    </form>
                    <form action="actions/pricing_delete_archived.php" method="POST" onsubmit="return confirm('تحذير! سيتم حذف كل تسعيرات هذا الصنف المؤرشفة نهائيًا. هل أنت متأكد؟');" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link-sm danger-btn">حذف نهائي</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>الكمية المشروطة</th>
                            <th>سعر الكرتونة</th>
                            <th>بونص (نفس الصنف)</th>
                            <th>أصناف بونص أخرى</th>
                            <th>تاريخ الأرشفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_group['offers'] as $offer): ?>
                            <?php foreach ($offer['levels'] as $level): ?>
                                <tr>
                                    <td><?php echo rtrim(rtrim(number_format($level['condition_quantity'], 2), '0'), '.'); ?></td>
                                    <td><?php echo number_format($level['price_per_unit'], 3); ?></td>
                                    <td><?php echo ($level['bonus_same_item_quantity'] > 0) ? rtrim(rtrim(number_format($level['bonus_same_item_quantity'], 2), '0'), '.') . ' كرتونة' : '-'; ?></td>
                                    <td>
                                        <?php if (!empty($level['bonus_items'])): ?>
                                            <ul>
                                            <?php foreach ($level['bonus_items'] as $bonus_item): ?>
                                                <li><?php echo htmlspecialchars($bonus_item['bonus_product_name']); ?>: <?php echo rtrim(rtrim(number_format($bonus_item['bonus_quantity'], 2), '0'), '.'); ?> كرتونة</li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: echo '-'; endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($offer['updated_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
/* يمكنك نقل هذا إلى style.css */
.archived-card {
    opacity: 0.8;
    border-left: 5px solid #6c757d; /* لون رمادي لتمييز المؤرشف */
}
<style>
/* ... نفس الستايلات السابقة ... */
.product-pricing-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    overflow: hidden;
}
.card-header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
}
.card-header h3 {
    margin: 0;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-header h3 span {
    font-size: 0.8em;
    color: #666;
    font-weight: normal;
}
.header-actions { display: flex; gap: 8px; }
.button-link-sm {
    padding: 5px 10px;
    font-size: 0.85em;
    text-decoration: none;
    color: white;
    border-radius: 4px;
    border: none;
    cursor: pointer;
}
.success-btn { background-color: #28a745; }
.secondary-btn { background-color: #6c757d; }
.danger-btn { background-color: #dc3545; }
.edit-btn { background-color: #ffc107; color: #212529; }
.card-body { padding: 0; }
.card-body table { margin: 0; border: none; }
.card-body table thead { background-color: #e9ecef; }
.card-body table td ul { margin: 0; padding-right: 15px; list-style-type: square; }
.card-body table td ul li { padding: 2px 0; }
</style>
