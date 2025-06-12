<?php // views/promotions/type_list.php ?>

<!-- ======================= نموذج الإضافة / التعديل ======================= -->
<div class="card" id="add_form">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($form_title); ?></h2>
    </div>
    <div class="card-body">
        <form action="actions/promotion_type_save.php" method="POST">
            <input type="hidden" name="action" value="<?php echo $form_action; ?>">
            <?php if ($form_action == 'edit' && isset($promo_type_data['promo_type_id'])): ?>
                <input type="hidden" name="promo_type_id" value="<?php echo $promo_type_data['promo_type_id']; ?>">
            <?php endif; ?>
            <?php csrf_input(); ?>

            <div class="form-group">
                <label for="name">اسم نوع الدعاية:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($promo_type_data['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">الوصف (اختياري):</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($promo_type_data['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_annual" value="1" <?php echo (isset($promo_type_data['is_annual']) && $promo_type_data['is_annual']) ? 'checked' : ''; ?>>
                    <span>مخصص للحملات السنوية فقط</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="button-link add-btn">
                    <i class="fas fa-save"></i> <?php echo $form_action == 'add' ? 'إضافة النوع' : 'حفظ التعديلات'; ?>
                </button>
                <?php // عرض زر الإلغاء فقط في وضع التعديل ?>
                <?php if ($form_action == 'edit'): ?>
                    <a href="index.php?page=promotion_types" class="button-link" style="background-color:#6c757d;">إلغاء التعديل</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- ======================= قائمة الأنواع الحالية ======================= -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2>قائمة أنواع الدعاية الحالية</h2>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الوصف</th>
                        <th>نوع الحملة</th>
                        <th>عدد الحملات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotion_types as $type): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($type['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($type['description'] ?? '')); ?></td>
                        <td>
                            <span class="badge <?php echo $type['is_annual'] ? 'status-active' : 'status-info'; ?>">
                                <?php echo $type['is_annual'] ? 'سنوية' : 'مؤقتة'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($type['campaign_count']); ?></td>
                        <td class="actions-cell">
                            <a href="index.php?page=promotion_types&action=edit&id=<?php echo $type['promo_type_id']; ?>" class="button-link edit-btn">تعديل</a>
                            
                            <?php // تعطيل زر الحذف إذا كان النوع مستخدماً ?>
                            <?php if ($type['campaign_count'] > 0): ?>
                                <button class="button-link delete-btn" disabled title="لا يمكن الحذف لوجود حملات مرتبطة">حذف</button>
                            <?php else: ?>
                                <form action="actions/promotion_type_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');" style="display:inline;">
                                    <input type="hidden" name="promo_type_id" value="<?php echo $type['promo_type_id']; ?>">
                                    <?php csrf_input(); ?>
                                    <button type="submit" class="button-link delete-btn">حذف</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($promotion_types)): ?>
                    <tr><td colspan="5">لم يتم إضافة أنواع دعاية بعد.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* يمكنك نقل هذا إلى style.css */
.card { border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.card-header { padding: 15px 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
.card-header h2 { margin: 0; font-size: 1.2em; }
.card-body { padding: 20px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; }
.form-actions { margin-top: 20px; }
.badge.status-info { background-color: var(--info-color); }
</style>