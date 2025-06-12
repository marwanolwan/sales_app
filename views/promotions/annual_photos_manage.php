<?php // views/promotions/annual_photos_manage.php

$start_date_obj = new DateTime($campaign_data['start_date']);
$end_date_obj = new DateTime($campaign_data['end_date']);
$months_in_campaign = [];
$current = clone $start_date_obj;
while ($current <= $end_date_obj) {
    $year = $current->format('Y');
    $month_num = $current->format('n');
    $months_in_campaign[] = ['year' => $year, 'month' => $month_num];
    $current->modify('first day of next month');
}

// إنشاء خريطة لعدد الصور في كل شهر لسهولة الوصول
$photos_count_map = [];
foreach ($months_with_photos as $item) {
    $photos_count_map[$item['year'] . '-' . $item['month']] = $item['photo_count'];
}

$month_names_ar = [1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل', 5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس', 9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر'];

?>
<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($page_title); ?> <small>(الزبون: <?php echo htmlspecialchars($customer['name']); ?>)</small></h2>
        <div class="actions">
             <a href="index.php?page=annual_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> قائمة الحملات
            </a>
        </div>
    </div>
    <div class="card-body">
        
        <!-- عرض الشهور -->
        <div class="months-grid">
            <?php foreach ($months_in_campaign as $mc): ?>
                <?php
                    $is_active = ($mc['year'] == $year && $mc['month'] == $month);
                    $count = $photos_count_map[$mc['year'] . '-' . $mc['month']] ?? 0;
                    $url = "index.php?page=annual_campaigns&action=photos&customer_id={$customer_id}&campaign_id={$campaign_id}&year={$mc['year']}&month={$mc['month']}";
                ?>
                <a href="<?php echo $url; ?>" class="month-card <?php echo $is_active ? 'active' : ''; ?>">
                    <span class="month-name"><?php echo $month_names_ar[$mc['month']] . ' ' . $mc['year']; ?></span>
                    <span class="photo-count">(<?php echo $count; ?> صور)</span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!isset($_GET['month'])): ?>
            <div class="info-message" style="margin-top:20px; text-align:center;">
                الرجاء اختيار شهر من الأعلى لعرض أو إدارة صوره.
            </div>
        <?php else: ?>
            <hr>
            <!-- قسم رفع وإدارة الصور للشهر المحدد -->
            <div class="photos-management-section">
                <h3>عرض الصور لشهر: <?php echo $month_names_ar[$month] . ' ' . $year; ?></h3>
                
                <div class="upload-form-container">
                    <h4>رفع صور جديدة لهذا الشهر</h4>
                    <form action="actions/annual_photo_upload.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                        <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <?php csrf_input(); ?>
                        <div class="form-group">
                            <label for="images">اختر الصور (يمكن تحديد أكثر من صورة):</label>
                            <input type="file" id="images" name="images[]" multiple required accept="image/jpeg,image/png,image/gif">
                        </div>
                        <button type="submit" class="button-link add-btn">رفع الصور</button>
                    </form>
                </div>
                
                <h4>الصور الحالية (<?php echo count($photos); ?>)</h4>
                <?php if (empty($photos)): ?>
                    <p class="info-message">لا توجد صور مرفوعة لهذا الشهر.</p>
                <?php else: ?>
                <div class="photos-grid">
                    <?php foreach ($photos as $photo): ?>
                    <div class="photo-card">
                        <a href="uploads/annual_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" target="_blank">
                            <img src="uploads/annual_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="صورة حملة سنوية">
                        </a>
                        <div class="photo-actions">
                            <a href="uploads/annual_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" download class="button-link">تحميل</a>
                            <form action="actions/annual_photo_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>">
                                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                                <input type="hidden" name="year" value="<?php echo $year; ?>">
                                <input type="hidden" name="month" value="<?php echo $month; ?>">
                                <?php csrf_input(); ?>
                                <button type="submit" class="button-link delete-btn">حذف</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* يمكنك نقل هذه الأنماط إلى style.css */
.months-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 8px;
}
.month-card {
    flex: 1 1 120px; /* Grow, shrink, base width */
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
    text-decoration: none;
    color: #333;
    background-color: #fff;
    transition: all 0.2s;
}
.month-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.month-card.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    font-weight: bold;
}
.month-card .month-name {
    display: block;
    font-size: 1em;
}
.month-card .photo-count {
    display: block;
    font-size: 0.8em;
    color: #777;
}
.month-card.active .photo-count {
    color: #e0e0e0;
}

.upload-form-container { padding: 20px; background-color: #f9f9f9; border-radius: 8px; margin: 20px 0; }
.photos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
.photo-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; text-align: center; }
.photo-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
.photo-card .photo-actions { padding: 10px; display: flex; justify-content: space-around; background-color: #f8f9fa; }
</style>