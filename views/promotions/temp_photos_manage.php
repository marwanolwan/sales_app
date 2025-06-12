<?php // views/promotions/temp_photos_manage.php ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $page_title; ?></h2>
        <div class="actions">
            <a href="index.php?page=temp_campaigns&customer_id=<?php echo $customer_id; ?>" class="button-link">
                <i class="fas fa-list"></i> قائمة الحملات المؤقتة
            </a>
            <a href="index.php?page=promotions&customer_id=<?php echo $customer_id; ?>" class="button-link" style="background-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> ملف الزبون
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="upload-form-container">
            <h3>رفع صور جديدة</h3>
            <form action="actions/temp_photo_upload.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                <?php csrf_input(); ?>
                <div class="form-group">
                    <label for="images">اختر الصور (يمكن تحديد أكثر من صورة):</label>
                    <input type="file" id="images" name="images[]" multiple required accept="image/jpeg,image/png,image/gif">
                </div>
                <button type="submit" class="button-link add-btn">رفع الصور</button>
            </form>
        </div>

        <hr>

        <h3>الصور الحالية (<?php echo count($photos); ?>)</h3>
        <?php if (empty($photos)): ?>
            <p class="info-message">لا توجد صور لهذه الحملة بعد.</p>
        <?php else: ?>
        <div class="photos-grid">
            <?php foreach ($photos as $photo): ?>
            <div class="photo-card">
                <a href="uploads/temp_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" target="_blank">
                    <img src="uploads/temp_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="صورة حملة">
                </a>
                <div class="photo-actions">
                    <a href="uploads/temp_campaigns_photos/<?php echo htmlspecialchars($photo['image_path']); ?>" download class="button-link">تحميل</a>
                    <form action="actions/temp_photo_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد؟');" style="display:inline;">
                        <input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>">
                        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                        <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                        <?php csrf_input(); ?>
                        <button type="submit" class="button-link delete-btn">حذف</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.upload-form-container { padding: 20px; background-color: #f9f9f9; border-radius: 8px; margin-bottom: 20px; }
.photos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
.photo-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; text-align: center; }
.photo-card img { width: 100%; height: 180px; object-fit: cover; }
.photo-card .photo-actions { padding: 10px; display: flex; justify-content: space-around; background-color: #f8f9fa; }
</style>