<?php // views/market_surveys/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=market_surveys&action=add" class="button-link add-btn">إنشاء دراسة سوق جديدة</a>
</div>

<table>
    <thead>
        <tr>
            <th>تاريخ الدراسة</th>
            <th>منتجنا</th>
            <th>نقطة البيع</th>
            <th>عدد المنافسين</th>
            <th>تمت بواسطة</th>
            <th>إجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($surveys as $survey): ?>
        <tr>
            <td><?php echo htmlspecialchars($survey['survey_date']); ?></td>
            <td><?php echo htmlspecialchars($survey['product_name']); ?></td>
            <td><?php echo htmlspecialchars($survey['customer_name'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($survey['competitor_count']); ?></td>
            <td><?php echo htmlspecialchars($survey['user_name']); ?></td>
            <td class="actions-cell">
                <a href="index.php?page=market_surveys&action=view&id=<?php echo $survey['survey_id']; ?>" class="button-link">عرض</a>
                
                <!-- زر استعراض الصور الجديد -->
                <button type="button" class="button-link" style="background-color: #5bc0de;" onclick="showImagesModal(<?php echo $survey['survey_id']; ?>)">الصور</button>
                
                <a href="index.php?page=market_surveys&action=edit&id=<?php echo $survey['survey_id']; ?>" class="button-link edit-btn">تعديل</a>

                <!-- زر الحذف الجديد -->
                <form action="actions/market_survey_delete.php" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الدراسة وكل ما يتعلق بها من منافسين وصور؟');" style="display:inline;">
                    <input type="hidden" name="survey_id" value="<?php echo $survey['survey_id']; ?>">
                    <?php csrf_input(); ?>
                    <button type="submit" class="button-link delete-btn">حذف</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($surveys)): ?>
            <tr><td colspan="6">لا توجد دراسات سوق لعرضها.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- =====| بداية النافذة المنبثقة (Modal) |===== -->
<div id="imagesModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeImagesModal()">×</span>
        <h3>صور المنافسين للدراسة</h3>
        <div id="modal-body" class="modal-images-grid">
            <!-- سيتم ملء الصور هنا عبر JavaScript -->
            <p>جاري تحميل الصور...</p>
        </div>
    </div>
</div>
<!-- =====| نهاية النافذة المنبثقة (Modal) |===== -->


<!-- =====| بداية كود JavaScript و CSS |===== -->
<script>
function showImagesModal(surveyId) {
    const modal = document.getElementById('imagesModal');
    const modalBody = document.getElementById('modal-body');
    
    modal.style.display = "block";
    modalBody.innerHTML = '<p>جاري تحميل الصور...</p>';

    // استخدام fetch لجلب بيانات الصور من ملف AJAX
    fetch(`actions/market_survey_get_images.php?survey_id=${surveyId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            modalBody.innerHTML = ''; // تفريغ المحتوى الحالي
            if (data.success && data.images.length > 0) {
                data.images.forEach(image => {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'modal-image-item';
                    
                    const a = document.createElement('a');
                    a.href = `<?php echo SURVEY_IMAGE_DIR; ?>${image.image_path}`;
                    a.target = '_blank';
                    
                    const img = document.createElement('img');
                    img.src = `<?php echo SURVEY_IMAGE_DIR; ?>${image.image_path}`;
                    img.alt = `صورة لـ ${image.competitor_product_name}`;
                    
                    const p = document.createElement('p');
                    p.textContent = image.competitor_product_name;

                    a.appendChild(img);
                    imgContainer.appendChild(a);
                    imgContainer.appendChild(p);
                    modalBody.appendChild(imgContainer);
                });
            } else {
                modalBody.innerHTML = '<p>لا توجد صور لعرضها لهذه الدراسة.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching images:', error);
            modalBody.innerHTML = '<p>حدث خطأ أثناء تحميل الصور.</p>';
        });
}

function closeImagesModal() {
    document.getElementById('imagesModal').style.display = "none";
}

// إغلاق النافذة عند الضغط خارجها
window.onclick = function(event) {
    const modal = document.getElementById('imagesModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */
.actions-cell {
    display: flex;
    gap: 5px;
    align-items: center;
    flex-wrap: wrap;
}
.actions-cell .button-link {
    padding: 5px 10px;
    font-size: 0.9em;
}

/* Modal Styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
    padding-top: 60px;
}
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 900px;
    border-radius: 5px;
    position: relative;
}
.close-modal {
    color: #aaa;
    position: absolute;
    left: 15px; /* تم التعديل ليتناسب مع RTL */
    top: 10px;
    font-size: 28px;
    font-weight: bold;
}
.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
.modal-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    max-height: 70vh;
    overflow-y: auto;
    padding: 10px;
}
.modal-image-item {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    text-align: center;
}
.modal-image-item img {
    max-width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
    margin-bottom: 5px;
}
.modal-image-item p {
    margin: 0;
    font-size: 0.9em;
    color: #333;
}
</style>