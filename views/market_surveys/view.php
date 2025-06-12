<?php // views/market_surveys/view.php ?>

<h2><?php echo htmlspecialchars($page_title); ?>: <?php echo htmlspecialchars($survey_data['product_name']); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=market_surveys" class="button-link" style="background-color:#6c757d;">العودة للقائمة</a>
    <a href="index.php?page=market_surveys&action=edit&id=<?php echo $survey_data['survey_id']; ?>" class="button-link edit-btn">تعديل هذه الدراسة</a>
</div>

<div class="survey-details">
    <h3>معلومات أساسية</h3>
    <p><strong>منتجنا:</strong> <?php echo htmlspecialchars($survey_data['product_name'] . ' (' . $survey_data['product_code'] . ')'); ?></p>
    <p><strong>تاريخ الدراسة:</strong> <?php echo htmlspecialchars($survey_data['survey_date']); ?></p>
    <p><strong>نقطة البيع:</strong> <?php echo htmlspecialchars($survey_data['customer_name'] ?? 'غير محددة'); ?></p>
    <p><strong>تمت بواسطة:</strong> <?php echo htmlspecialchars($survey_data['user_name']); ?></p>
    <?php if(!empty($survey_data['notes'])): ?>
        <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($survey_data['notes'])); ?></p>
    <?php endif; ?>
</div>

<h3>مقارنة الأسعار وهوامش الربح</h3>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الصورة</th>
                <th>سعر جملة</th>
                <th>سعر تجزئة</th>
                <th>سعر رف</th>
                <th>ربح تاجر الجملة</th>
                <th>ربح تاجر التجزئة</th>
                <th>عدد الأوجه</th>
                <th>التصنيف التنافسي</th>
            </tr>
        </thead>
        <tbody>
            <!-- منتجنا -->
            <tr class="our-product-row">
                <td><strong><?php echo htmlspecialchars($survey_data['product_name']); ?> (منتجنا)</strong></td>
                <td>-</td>
                <td><?php echo number_format($survey_data['our_wholesale_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format($survey_data['our_retail_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format($survey_data['our_shelf_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format(($survey_data['our_retail_price'] ?? 0) - ($survey_data['our_wholesale_price'] ?? 0), 2); ?></td>
                <td><?php echo number_format(($survey_data['our_shelf_price'] ?? 0) - ($survey_data['our_retail_price'] ?? 0), 2); ?></td>
                <td>-</td>
                <td>-</td>
            </tr>
            <!-- منتجات المنافسين -->
            <?php foreach($competitors_data as $comp): ?>
            <tr>
                <td><?php echo htmlspecialchars($comp['competitor_product_name']); ?></td>
                <td>
                    <?php if (!empty($comp['image_path']) && file_exists(SURVEY_IMAGE_DIR . $comp['image_path'])): ?>
                        <a href="<?php echo SURVEY_IMAGE_DIR . $comp['image_path']; ?>" target="_blank">
                            <img src="<?php echo SURVEY_IMAGE_DIR . $comp['image_path']; ?>" alt="صورة منافس" style="max-width:80px; max-height:80px;">
                        </a>
                    <?php else: echo 'N/A'; endif; ?>
                </td>
                
                <!-- =====| بداية التعديلات |===== -->
                <td><?php echo number_format($comp['wholesale_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format($comp['retail_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format($comp['shelf_price'] ?? 0, 2); ?></td>
                <td><?php echo number_format(($comp['retail_price'] ?? 0) - ($comp['wholesale_price'] ?? 0), 2); ?></td>
                <td><?php echo number_format(($comp['shelf_price'] ?? 0) - ($comp['retail_price'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($comp['facings_on_shelf'] ?? '-'); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($comp['competitive_position'] ?? 'غير محدد'); ?></span></td>
                <!-- =====| نهاية التعديلات |===== -->

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.survey-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
.our-product-row { background-color: #d4edda; font-weight: bold; }
.badge { background-color: #007bff; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8em; }
</style>