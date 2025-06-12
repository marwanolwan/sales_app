<?php
// actions/market_survey_save.php
require_once '../core/db.php';
require_once '../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=market_surveys"); exit();
}

require_permission('manage_market_surveys');
verify_csrf_token();

$action = $_POST['action'] ?? 'add';
$survey_id = ($action == 'edit') ? (int)($_POST['survey_id'] ?? null) : null;
define('SURVEY_IMAGE_DIR', '../uploads/market_surveys/');

try {
    $pdo->beginTransaction();

    $survey_data = [
        'product_id' => $_POST['product_id'],
        'survey_date' => $_POST['survey_date'],
        'user_id' => $_SESSION['user_id'],
        'customer_id' => !empty($_POST['customer_id']) ? $_POST['customer_id'] : null,
        'our_wholesale_price' => !empty($_POST['our_wholesale_price']) ? $_POST['our_wholesale_price'] : null,
        'our_retail_price' => !empty($_POST['our_retail_price']) ? $_POST['our_retail_price'] : null,
        'our_shelf_price' => !empty($_POST['our_shelf_price']) ? $_POST['our_shelf_price'] : null,
        'notes' => trim($_POST['notes'])
    ];

    if ($action == 'add') {
        $sql = "INSERT INTO market_surveys (product_id, survey_date, user_id, customer_id, our_wholesale_price, our_retail_price, our_shelf_price, notes) 
                VALUES (:product_id, :survey_date, :user_id, :customer_id, :our_wholesale_price, :our_retail_price, :our_shelf_price, :notes)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($survey_data);
        $survey_id = $pdo->lastInsertId();
    } elseif ($action == 'edit' && $survey_id) {
        $sql = "UPDATE market_surveys SET product_id=:product_id, survey_date=:survey_date, customer_id=:customer_id, our_wholesale_price=:our_wholesale_price, 
                our_retail_price=:our_retail_price, our_shelf_price=:our_shelf_price, notes=:notes WHERE survey_id=:survey_id";
        $survey_data['survey_id'] = $survey_id;
        // User ID should not be updated on edit
        unset($survey_data['user_id']);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($survey_data);
    }

    $competitors = $_POST['competitor'] ?? [];
    $existing_ids = [];

    foreach ($competitors as $index => $comp_data) {
        $entry_id = $comp_data['id'];
        
        $params = [
            'survey_id' => $survey_id,
            'competitor_product_name' => $comp_data['name'],
            'wholesale_price' => !empty($comp_data['wholesale']) ? $comp_data['wholesale'] : null,
            'retail_price' => !empty($comp_data['retail']) ? $comp_data['retail'] : null,
            'shelf_price' => !empty($comp_data['shelf']) ? $comp_data['shelf'] : null,
            'facings_on_shelf' => !empty($comp_data['facings']) ? $comp_data['facings'] : null,
            'competitive_position' => !empty($comp_data['position']) ? $comp_data['position'] : null,
        ];

        if ($entry_id == 'new') {
            $sql_comp = "INSERT INTO market_survey_competitors (survey_id, competitor_product_name, wholesale_price, retail_price, shelf_price, facings_on_shelf, competitive_position)
                         VALUES (:survey_id, :competitor_product_name, :wholesale_price, :retail_price, :shelf_price, :facings_on_shelf, :competitive_position)";
            $stmt_comp = $pdo->prepare($sql_comp);
            $stmt_comp->execute($params);
            $competitor_entry_id = $pdo->lastInsertId();
            $existing_ids[] = $competitor_entry_id;
        } else {
            $competitor_entry_id = (int)$entry_id;
            $existing_ids[] = $competitor_entry_id;
            $params['entry_id'] = $competitor_entry_id;
            $sql_comp = "UPDATE market_survey_competitors SET competitor_product_name=:competitor_product_name, wholesale_price=:wholesale_price, retail_price=:retail_price, shelf_price=:shelf_price,
                         facings_on_shelf=:facings_on_shelf, competitive_position=:competitive_position WHERE competitor_entry_id=:entry_id AND survey_id=:survey_id";
            $stmt_comp = $pdo->prepare($sql_comp);
            $stmt_comp->execute($params);
        }
        
        if (isset($_FILES['competitor_images_' . $index]) && is_array($_FILES['competitor_images_' . $index]['name'])) {
            $files = $_FILES['competitor_images_' . $index];
            $stmt_image = $pdo->prepare("INSERT INTO market_survey_images (competitor_entry_id, image_path) VALUES (?, ?)");
            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $new_filename = 'survey_' . $survey_id . '_comp_' . $competitor_entry_id . '_' . time() . '_' . uniqid() . '.' . $ext;
                    if (!is_dir(SURVEY_IMAGE_DIR)) mkdir(SURVEY_IMAGE_DIR, 0775, true);
                    if (move_uploaded_file($files['tmp_name'][$key], SURVEY_IMAGE_DIR . $new_filename)) {
                        $stmt_image->execute([$competitor_entry_id, $new_filename]);
                    }
                }
            }
        }
    }

    if ($action == 'edit' && $survey_id && !empty($existing_ids)) {
        $placeholders = implode(',', array_fill(0, count($existing_ids), '?'));
        
        $sql_get_imgs = "SELECT image_path FROM market_survey_competitors c LEFT JOIN market_survey_images i ON c.competitor_entry_id = i.competitor_entry_id WHERE c.survey_id = ? AND c.competitor_entry_id NOT IN ($placeholders)";
        $stmt_get_imgs = $pdo->prepare($sql_get_imgs);
        $stmt_get_imgs->execute(array_merge([$survey_id], $existing_ids));
        $images_to_delete = $stmt_get_imgs->fetchAll(PDO::FETCH_COLUMN);
        foreach ($images_to_delete as $img) {
            if ($img && file_exists(SURVEY_IMAGE_DIR . $img)) {
                unlink(SURVEY_IMAGE_DIR . $img);
            }
        }
        
        $sql_delete_old = "DELETE FROM market_survey_competitors WHERE survey_id = ? AND competitor_entry_id NOT IN ($placeholders)";
        $stmt_delete = $pdo->prepare($sql_delete_old);
        $stmt_delete->execute(array_merge([$survey_id], $existing_ids));
    }


    $pdo->commit();
    $_SESSION['success_message'] = "تم حفظ الدراسة بنجاح.";
    header("Location: ../index.php?page=market_surveys&action=view&id=" . $survey_id);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Survey save failed: " . $e->getMessage());
    $_SESSION['error_message'] = "حدث خطأ فني أثناء حفظ الدراسة: " . $e->getMessage();
    header("Location: ../index.php?page=market_surveys&action={$action}" . ($survey_id ? "&id={$survey_id}" : ''));
    exit();
}