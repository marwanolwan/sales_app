<?php // views/market_share/report.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="toolbar" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
    <div class="actions-bar">
        <a href="index.php?page=market_share&action=data_entry" class="button-link add-btn">إدخال / تعديل بيانات</a>
    </div>
    <div class="search-form">
        <form action="index.php" method="GET" class="form-row" style="gap: 15px; align-items: flex-end;">
            <input type="hidden" name="page" value="market_share">
            <div class="form-group">
                <label for="period_filter">الفترة:</label>
                <select name="period" id="period_filter" class="form-control">
                    <option value="">-- اختر فترة --</option>
                    <?php foreach($available_periods as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>" <?php echo ($filter_period == $p) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="rep_filter">المندوب:</label>
                <select name="rep_id" id="rep_filter" class="select2-enable">
                    <option value="all">-- كل المندوبين --</option>
                    <?php foreach($reps_for_filter as $r): ?>
                         <option value="<?php echo $r['user_id']; ?>" <?php echo ($filter_rep == $r['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="customer_search_filter">بحث عن عميل:</label>
                <input type="text" name="customer_search" id="customer_search_filter" placeholder="اسم العميل أو الكود..." value="<?php echo htmlspecialchars($filter_customer_search); ?>" class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="button-link">عرض التقرير</button>
            </div>
        </form>
    </div>
</div>

<div class="report-container">
    <?php if (!empty($report_data_grouped)): ?>
        <p><strong>تقرير الحصة السوقية لفترة: <?php echo htmlspecialchars($filter_period); ?></strong> (اضغط على اسم العميل لعرض التفاصيل)</p>
        
        <div class="accordion-container">
            <?php foreach($report_data_grouped as $customer_id => $data): ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <div class="header-main-content" onclick="toggleAccordion(this.parentElement)">
                            <span class="customer-name"><?php echo htmlspecialchars($data['customer_info']['name'] . ' (' . $data['customer_info']['code'] . ')'); ?></span>
                            <span class="total-quantity">إجمالي الكمية: <?php echo number_format($data['total_customer_quantity'], 2); ?></span>
                        </div>
                        
                        <div class="header-actions">
                            <a href="index.php?page=market_share&action=data_entry&customer_id=<?php echo $customer_id; ?>&report_period=<?php echo urlencode($filter_period); ?>" class="button-link edit-btn">تعديل</a>
                            
                            <form action="actions/market_share_delete_by_customer.php" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف جميع بيانات هذه الدراسة لهذا العميل في هذه الفترة؟');" style="display:inline;">
                                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                <input type="hidden" name="report_period" value="<?php echo $filter_period; ?>">
                                <?php csrf_input(); ?>
                                <button type="submit" class="button-link delete-btn">حذف</button>
                            </form>
                        </div>

                        <span class="accordion-icon" onclick="toggleAccordion(this.parentElement)">+</span>
                    </div>
                    <div class="accordion-content">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية المباعة</th>
                                        <th>الحصة السوقية لدى العميل (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['products'] as $product): ?>
                                    <tr class="<?php echo $product['is_our'] ? 'our-product-row' : ''; ?>">
                                        <td>
                                            <?php echo htmlspecialchars($product['name']); ?>
                                            <?php if ($product['is_our']): ?> <span class="badge-main">(منتجنا)</span> <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($product['quantity'], 2); ?></td>
                                        <td>
                                            <?php
                                            $share = ($data['total_customer_quantity'] > 0) ? ($product['quantity'] / $data['total_customer_quantity']) * 100 : 0;
                                            echo '<strong>' . number_format($share, 2) . '%</strong>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <p class="info-message">لا توجد بيانات لهذه الفترة أو الفلاتر المحددة.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Accordion functionality
    function toggleAccordion(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.accordion-icon');
        
        header.classList.toggle('active');
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            icon.textContent = '+';
        } else {
            // A slight delay to ensure the element is ready before calculating scrollHeight
            setTimeout(() => {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.textContent = '−';
            }, 5);
        }
    }

    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        const mainContent = header.querySelector('.header-main-content');
        const icon = header.querySelector('.accordion-icon');
        
        if (mainContent) {
            mainContent.addEventListener('click', () => toggleAccordion(header));
        }
        if (icon) {
            icon.addEventListener('click', () => toggleAccordion(header));
        }
    });

    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2-enable').select2({ 
            width: 'style',
            dir: 'rtl' 
        });
    }
});
</script>

<style>
/* ----------------- Toolbar & Filters ----------------- */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}
.actions-bar {
    flex-grow: 1;
}
.search-form .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}
.search-form .form-group {
    display: flex;
    flex-direction: column;
}
.search-form .form-group label {
    margin-bottom: 5px;
    font-size: 0.9em;
    color: #555;
}
.search-form select,
.search-form input[type="text"] {
    min-width: 180px;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
}
.select2-container {
    min-width: 180px;
}

/* ----------------- Report Container ----------------- */
.report-container {
    margin-top: 20px;
}
.report-container > p {
    font-size: 1.1em;
    color: #333;
}

/* ----------------- Accordion Styles ----------------- */
.accordion-container {
    width: 100%;
    margin-top: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden; /* For border-radius to work on children */
}
.accordion-item {
    border-bottom: 1px solid #ddd;
}
.accordion-item:last-child {
    border-bottom: none;
}
.accordion-header {
    background-color: #f7f7f7;
    color: #444;
    cursor: pointer;
    padding: 12px 20px;
    width: 100%;
    text-align: right;
    border: none;
    outline: none;
    transition: background-color 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}
.accordion-header:hover, .accordion-header.active {
    background-color: #e9ecef;
}
.accordion-header .header-main-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}
.accordion-header .customer-name {
    font-weight: bold;
    font-size: 1.1em;
    color: var(--primary-color);
}
.accordion-header .total-quantity {
    font-size: 0.9em;
    color: #555;
}
.accordion-header .header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-shrink: 0;
}
.header-actions .button-link {
    padding: 5px 12px;
    font-size: 0.85em;
    margin: 0;
}
.accordion-header .accordion-icon {
    font-size: 1.4em;
    font-weight: bold;
    color: #777;
    transition: transform 0.3s ease;
}
.accordion-header.active .accordion-icon {
    transform: rotate(45deg);
}

.accordion-content {
    padding: 0 18px;
    background-color: white;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}
.accordion-content .table-responsive {
    overflow-x: auto;
    padding: 15px 0;
}
.accordion-content table {
    margin: 0;
}

/* General Table & Badge Styles */
.our-product-row {
    background-color: #e2f0d9 !important; /* A slightly different green */
    font-weight: bold;
}
.badge-main {
    font-size: 0.8em;
    color: var(--primary-color);
    font-weight: normal;
    margin-right: 5px;
}
</style>