<?php // views/promotions/contract_form.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <a href="index.php?page=annual_contracts&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color:#6c757d;">
            <i class="fas fa-arrow-left"></i> العودة لقائمة العقود
        </a>
    </div>
    <div class="card-body">
        <form action="actions/annual_contract_save.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="contract_id" value="<?php echo $contract_id; ?>">
                <input type="hidden" name="current_file_path" value="<?php echo htmlspecialchars($contract_data['contract_file_path'] ?? ''); ?>">
            <?php endif; ?>
            <?php csrf_input(); ?>

            <div class="form-group">
                <label for="year">سنة العقد:</label>
                <select id="year" name="year" required>
                    <?php for ($y = date('Y') + 2; $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo (isset($contract_data['year']) && $contract_data['year'] == $y) ? 'selected' : ''; echo (!isset($contract_data) && $y == date('Y')) ? 'selected' : '' ?>>
                        <?php echo $y; ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <fieldset>
                <legend>الهدف الأول</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="target_1_value">قيمة الهدف (مبيعات):</label>
                        <input type="number" step="0.01" name="target_1_value" value="<?php echo htmlspecialchars($contract_data['target_1_value'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="target_1_bonus">نسبة الخصم (%):</label>
                        <input type="number" step="0.01" name="target_1_bonus" value="<?php echo htmlspecialchars($contract_data['target_1_bonus'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>الهدف الثاني</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="target_2_value">قيمة الهدف (مبيعات):</label>
                        <input type="number" step="0.01" name="target_2_value" value="<?php echo htmlspecialchars($contract_data['target_2_value'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="target_2_bonus">نسبة الخصم (%):</label>
                        <input type="number" step="0.01" name="target_2_bonus" value="<?php echo htmlspecialchars($contract_data['target_2_bonus'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>الهدف الثالث</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="target_3_value">قيمة الهدف (مبيعات):</label>
                        <input type="number" step="0.01" name="target_3_value" value="<?php echo htmlspecialchars($contract_data['target_3_value'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="target_3_bonus">نسبة الخصم (%):</label>
                        <input type="number" step="0.01" name="target_3_bonus" value="<?php echo htmlspecialchars($contract_data['target_3_bonus'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <div class="form-group">
                <label for="contract_file">ملف العقد (PDF, DOC, DOCX, JPG, PNG):</label>
                <input type="file" id="contract_file" name="contract_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <?php if ($action == 'edit' && !empty($contract_data['contract_file_path'])): ?>
                    <p class="current-file-info">
                        الملف الحالي: <a href="uploads/annual_contracts/<?php echo htmlspecialchars($contract_data['contract_file_path']); ?>" target="_blank"><?php echo htmlspecialchars($contract_data['contract_file_path']); ?></a>
                    </p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="notes">ملاحظات:</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($contract_data['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button-link add-btn">
                    <i class="fas fa-save"></i> <?php echo $action == 'add' ? 'إضافة العقد' : 'حفظ التعديلات'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<style>
fieldset { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
legend { padding: 0 10px; font-weight: bold; color: var(--primary-color); }
.current-file-info { margin-top: 10px; font-size: 0.9em; }
</style>