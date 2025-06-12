<?php
// views/layout.php
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | نظام المبيعات' : 'نظام إدارة المبيعات'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- jQuery (مطلوب لـ Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script> <!-- السطر الجديد -->

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Custom styles for Select2 RTL -->
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px; /* Match form input height */
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-dropdown {
            text-align: right;
        }
    </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <header class="main-header">
        <h1>نظام إدارة المبيعات</h1>
        <div class="user-info">
            مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)
            <a href="index.php?page=logout">تسجيل الخروج</a>
        </div>
    </header>
    <div class="main-container">
        <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
        <main class="content-area">
    <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
             <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="message info-message"><?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($view_file) && file_exists($view_file)) {
                include $view_file;
            } else {
                echo "<div class='message error-message'>خطأ: ملف الواجهة غير موجود.</div>";
            }
            ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        </main>
    </div>
    <footer class="main-footer-bottom">
        <p>© <?php echo date('Y'); ?> شركة XYZ. جميع الحقوق محفوظة.</p>
    </footer>
    <?php endif; ?>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Main Script File -->
    <script src="js/script.js"></script>
</body>
</html>