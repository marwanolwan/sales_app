<?php
// views/partials/pagination.php

// إذا لم يتم تمرير بارامترات، قم بإنشاء مصفوفة فارغة
$pagination_params = $pagination_params ?? [];

// دمج البارامترات الأساسية مع البارامترات الممررة
$base_params = [
    'page' => $_GET['page'] ?? 'home',
    'type' => $_GET['type'] ?? null, // مهم لمركز التقارير
    'action' => $_GET['action'] ?? null,
];
$filter_params = [
    'search' => $search_term ?? null,
    'region_id' => $filter_region_id ?? null,
    'supervisor_id' => $filter_supervisor_id ?? null,
    'representative_id' => $filter_rep_id ?? null,
    'promoter_id' => $filter_promoter_id ?? null,
    'family_id' => $filter_family_id ?? null,
    'limit' => $filter_limit ?? null
];

$all_params = array_merge($base_params, $filter_params);
$all_params = array_filter($all_params, function($value) {
    return $value !== null && $value !== '' && $value !== 'all';
});

// إزالة أي بارامترات فارغة
$all_params = array_filter($all_params, function($value) {
    return $value !== null && $value !== '';
});
?>
<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <div class="page-info">
            صفحة <?php echo $page_num; ?> من <?php echo $total_pages; ?> (إجمالي <?php echo $total_items; ?> عنصر)
        </div>
        <div class="page-links">
            <?php
            // بناء رابط الصفحة الأولى
            $first_page_query = http_build_query(array_merge($all_params, ['p' => 1]));
            // بناء رابط الصفحة السابقة
            $prev_page_query = http_build_query(array_merge($all_params, ['p' => $page_num - 1]));
            ?>
            <?php if ($page_num > 1): ?>
                <a href="index.php?<?php echo $first_page_query; ?>">«</a>
                <a href="index.php?<?php echo $prev_page_query; ?>">‹</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);
            
            if ($start > 1) echo "<span>...</span>";
            for ($i = $start; $i <= $end; $i++):
                $current_page_query = http_build_query(array_merge($all_params, ['p' => $i]));
            ?>
                <a href="index.php?<?php echo $current_page_query; ?>" class="<?php echo ($i == $page_num) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($end < $total_pages) echo "<span>...</span>"; ?>

            <?php if ($page_num < $total_pages): ?>
                 <?php
                // بناء رابط الصفحة التالية
                $next_page_query = http_build_query(array_merge($all_params, ['p' => $page_num + 1]));
                // بناء رابط الصفحة الأخيرة
                $last_page_query = http_build_query(array_merge($all_params, ['p' => $total_pages]));
                ?>
                <a href="index.php?<?php echo $next_page_query; ?>">›</a>
                <a href="index.php?<?php echo $last_page_query; ?>">»</a>
            <?php endif; ?>
        </div>
    <?php elseif($total_items > 0): ?>
        <div class="page-info">
             إجمالي <?php echo $total_items; ?> عنصر
        </div>
    <?php endif; ?>
</div>