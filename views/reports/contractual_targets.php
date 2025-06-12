<?php // views/reports/contractual_targets.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php include __DIR__ . '/_filters.php'; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3>نتائج التقرير لسنة <?php echo htmlspecialchars($filter_year); ?></h3>
    </div>
    <div class="card-body">
        <?php if(empty($report_data)): ?>
            <p class="info-message">لا توجد عقود أو بيانات مبيعات تطابق الفلاتر المحددة.</p>
        <?php else: ?>
        <div class="contracts-container">
            <?php foreach($report_data as $contract): 
                $total_sales = (float)$contract['total_annual_sales'];
            ?>
            <div class="contract-card">
                <h4>
                    <a href="index.php?page=promotions&customer_id=<?php echo $contract['customer_id']; ?>">
                        <?php echo htmlspecialchars($contract['customer_name']); ?>
                    </a>
                    <span class="year-badge"><?php echo htmlspecialchars($contract['year']); ?></span>
                </h4>
                <div class="sales-summary">
                    إجمالي المبيعات السنوية المحققة: <strong><?php echo number_format($total_sales, 2); ?></strong>
                </div>
                
                <div class="targets-section">
                    <?php for ($i = 1; $i <= 3; $i++): 
                        $target_value = (float)($contract["target_{$i}_value"] ?? 0);
                        $target_bonus = (float)($contract["target_{$i}_bonus"] ?? 0);
                        if ($target_value > 0):
                            $percentage = ($total_sales / $target_value) * 100;
                    ?>
                        <div class="target-item">
                            <div class="target-details">
                                <span>الهدف <?php echo $i; ?>: <?php echo number_format($target_value, 2); ?> (خصم <?php echo $target_bonus; ?>%)</span>
                                <span class="percentage-label <?php echo $percentage >= 100 ? 'achieved' : ''; ?>">
                                    <?php echo number_format($percentage, 2); ?>%
                                </span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo min(100, $percentage); ?>%;"></div>
                            </div>
                        </div>
                    <?php endif; endfor; ?>
                </div>

                <?php if ($contract['contract_file_path']): ?>
                    <div class="contract-file-link">
                        <a href="uploads/annual_contracts/<?php echo htmlspecialchars($contract['contract_file_path']); ?>" target="_blank">
                           <i class="fas fa-file-pdf"></i> عرض ملف العقد
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.contracts-container { display: grid; gap: 20px; }
.contract-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
.contract-card h4 { margin-top: 0; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; }
.year-badge { background-color: #6c757d; color: white; padding: 3px 8px; font-size: 0.8em; border-radius: 5px; }
.sales-summary { margin: 15px 0; font-size: 1.1em; text-align: center; background: #f8f9fa; padding: 10px; border-radius: 5px;}
.target-item { margin-bottom: 15px; }
.target-details { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em; }
.percentage-label.achieved { color: var(--success-color); font-weight: bold; }
.progress-bar-container { background-color: #e9ecef; border-radius: 5px; }
.progress-bar { background-color: var(--primary-color); height: 10px; border-radius: 5px; }
.contract-file-link { margin-top: 15px; text-align: right; }
</style>