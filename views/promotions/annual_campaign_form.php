<?php // views/promotions/annual_campaign_form.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <a href="index.php?page=annual_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color:#6c757d;">
            <i class="fas fa-arrow-left"></i> العودة لقائمة الحملات
        </a>
    </div>
    <div class="card-body">
        <form action="actions/annual_campaign_save.php" method="POST" id="annualCampaignForm">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
            <?php endif; ?>
            <?php csrf_input(); ?>

            <div class="form-group">
                <label for="promo_type_id">نوع الدعاية السنوية:</label>
                <select id="promo_type_id" name="promo_type_id" required>
                    <option value="">-- اختر النوع --</option>
                    <?php foreach ($annual_promo_types as $type): ?>
                    <option value="<?php echo $type['promo_type_id']; ?>" <?php echo (isset($campaign_data['promo_type_id']) && $campaign_data['promo_type_id'] == $type['promo_type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">تاريخ البدء:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($campaign_data['start_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">تاريخ الانتهاء:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($campaign_data['end_date'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="contract_duration_months">مدة العقد (أشهر):</label>
                    <input type="number" id="contract_duration_months" name="contract_duration_months" value="<?php echo htmlspecialchars($campaign_data['contract_duration_months'] ?? '12'); ?>" required readonly>
                </div>
            </div>

             <div class="form-row">
                <div class="form-group">
                    <label for="monthly_value">الإيجار / القيمة الشهرية:</label>
                    <input type="number" step="0.01" id="monthly_value" name="monthly_value" value="<?php echo htmlspecialchars($campaign_data['monthly_value'] ?? '0.00'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="total_value">إجمالي قيمة العقد:</label>
                    <input type="number" step="0.01" id="total_value" name="total_value" value="<?php echo htmlspecialchars($campaign_data['total_value'] ?? '0.00'); ?>" required readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">ملاحظات:</label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($campaign_data['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button-link add-btn">
                    <i class="fas fa-save"></i> <?php echo $action == 'add' ? 'إضافة الحملة' : 'حفظ التعديلات'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const durationInput = document.getElementById('contract_duration_months');
    const monthlyValueInput = document.getElementById('monthly_value');
    const totalValueInput = document.getElementById('total_value');

    function calculateValues() {
        // Calculate duration in months
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            
            if (end > start) {
                let months = (end.getFullYear() - start.getFullYear()) * 12;
                months -= start.getMonth();
                months += end.getMonth();
                durationInput.value = months <= 0 ? 0 : months;
            } else {
                durationInput.value = 0;
            }
        }

        // Calculate total value
        const duration = parseFloat(durationInput.value) || 0;
        const monthlyValue = parseFloat(monthlyValueInput.value) || 0;
        totalValueInput.value = (duration * monthlyValue).toFixed(2);
    }

    startDateInput.addEventListener('change', calculateValues);
    endDateInput.addEventListener('change', calculateValues);
    monthlyValueInput.addEventListener('input', calculateValues);
    
    // Initial calculation on page load for edit forms
    calculateValues();
});
</script>