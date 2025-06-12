<?php // views/posm/dashboard.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=posm&action=stock_entry" class="button-link add-btn">إدخال حركة مخزون جديدة</a>
    <a href="index.php?page=posm&action=items_list" class="button-link" style="background-color:#17a2b8;">إدارة المواد الترويجية</a>
</div>

<div class="posm-dashboard-container">
    
    <!-- =====| العمود الأول: رصيد المخزن الرئيسي |===== -->
    <div class="posm-card">
        <h3>📊 رصيد المخزن الرئيسي</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>المادة الترويجية</th>
                        <th>الرصيد الحالي</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stock_summary)): ?>
                        <tr><td colspan="3">لم يتم تعريف أي مواد ترويجية بعد.</td></tr>
                    <?php else: ?>
                        <?php foreach($stock_summary as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td>
                                    <strong>
                                        <?php 
                                            $stock = (int)($item['current_stock'] ?? 0);
                                            echo $stock;
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <a href="index.php?page=posm&action=history&id=<?php echo $item['item_id']; ?>" class="button-link" style="font-size: 0.8em; padding: 4px 8px;">
                                        عرض السجل
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- =====| العمود الثاني: أرصدة المندوبين |===== -->
    <div class="posm-card">
        <h3>🚚 الأرصدة في عهدة المروجين</h3>
        <div class="accordion-container">
            <?php if (empty($promoter_stock_grouped)): ?>
                <div class="empty-state">
                    <p>لا توجد أرصدة حالية في عهدة أي مروج.</p>
                </div>
            <?php else: ?>
                <?php foreach($promoter_stock_grouped as $promoter_name => $items): ?>
                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span class="rep-name"><?php echo htmlspecialchars($promoter_name); ?></span>
                            <span class="item-count"><?php echo count($items); ?> مواد مختلفة</span>
                            <span class="accordion-icon">+</span>
                        </button>
                        <div class="accordion-content">
                            <ul>
                                <?php foreach($items as $item): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                                        <strong><?php echo (int)$item['promoter_balance']; ?> قطعة</strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.accordion-icon');
            
            header.classList.toggle('active');
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                icon.textContent = '+';
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.textContent = '−';
            }
        });
    });
});
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.actions-bar {
    margin-bottom: 20px;
}
.posm-dashboard-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
}
.posm-card {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
}
.posm-card h3 {
    margin-top: 0;
    border-bottom: 2px solid #f1f1f1;
    padding-bottom: 10px;
    margin-bottom: 15px;
    color: var(--primary-color);
}
.posm-card .table-container {
    max-height: 50vh;
    overflow-y: auto;
}

/* Accordion for Reps */
.accordion-container {
    width: 100%;
    max-height: 50vh;
    overflow-y: auto;
}
.accordion-item {
    border-bottom: 1px solid #eee;
}
.accordion-item:last-child {
    border-bottom: none;
}
.accordion-header {
    background-color: transparent;
    color: #444;
    cursor: pointer;
    padding: 15px 5px;
    width: 100%;
    text-align: right;
    border: none;
    outline: none;
    transition: background-color 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.accordion-header:hover, .accordion-header.active {
    background-color: #f8f9fa;
}
.accordion-header .rep-name {
    font-weight: bold;
}
.accordion-header .item-count {
    font-size: 0.85em;
    color: #777;
    background-color: #e9ecef;
    padding: 3px 8px;
    border-radius: 10px;
}
.accordion-header .accordion-icon {
    font-size: 1.2em;
    font-weight: bold;
}
.accordion-content {
    padding: 0 5px 15px 5px;
    background-color: white;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}
.accordion-content ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.accordion-content li {
    display: flex;
    justify-content: space-between;
    padding: 8px 5px;
    border-bottom: 1px dotted #f1f1f1;
}
.accordion-content li:last-child {
    border-bottom: none;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}
</style>