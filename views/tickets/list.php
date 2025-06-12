<?php // views/tickets/list.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<div class="actions-bar">
    <a href="index.php?page=tickets&action=add" class="button-link add-btn">إنشاء تذكرة جديدة</a>
</div>

 <div class="view-toggle">
        <a href="index.php?page=tickets&action=list&view=active" 
           class="button-link <?php echo ($view_mode === 'active') ? 'active' : ''; ?>">
           النشطة
        </a>
        <a href="index.php?page=tickets&action=list&view=archived" 
           class="button-link <?php echo ($view_mode === 'archived') ? 'active' : ''; ?>">
           الأرشيف (المغلقة)
        </a>
    </div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الموضوع</th>
                <th>العميل</th>
                <th>الأولوية</th>
                <th>الحالة</th>
                <th>أنشئت بواسطة</th>
                <th>آخر تحديث</th>
                <th>تاريخ الإنشاء</th>
                <th style="width: 100px;">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets_list)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        لا توجد تذاكر لعرضها حاليًا.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($tickets_list as $ticket): ?>
                    <tr>
                        <td><strong><?php echo $ticket['ticket_id']; ?></strong></td>
                        <td>
                            <a href="index.php?page=tickets&action=view&id=<?php echo $ticket['ticket_id']; ?>" class="ticket-title-link">
                                <?php echo htmlspecialchars($ticket['subject']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($ticket['customer_name'] ?? 'لا يوجد عميل محدد'); ?></td>
                        <td>
                                                        <span class="priority-badge priority-<?php echo strtolower($ticket['priority']); ?>">
                                <?php echo htmlspecialchars($priorities[$ticket['priority']] ?? $ticket['priority']); ?>

                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                <?php echo htmlspecialchars($statuses[$ticket['status']] ?? $ticket['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($ticket['creator_name']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></td>
                        
                        
                        <td>
                            <a href="index.php?page=tickets&action=view&id=<?php echo $ticket['ticket_id']; ?>" class="button-link">
                                عرض التفاصيل
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =====| بداية كود CSS المدمج |===== -->
<style>
/* يمكنك نقل هذا الكود إلى ملف style.css الرئيسي */

/* Table Styles */
.table-container {
    overflow-x: auto;
}
.ticket-title-link {
    font-weight: bold;
    color: var(--primary-color);
    text-decoration: none;
}
.ticket-title-link:hover {
    text-decoration: underline;
}

/* Badge Styles */
.priority-badge, .status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    color: white;
    font-size: 0.8em;
    font-weight: 500;
    text-align: center;
    min-width: 80px;
}

/* Priority Colors */
.priority-low { background-color: #17a2b8; } /* Info */
.priority-medium { background-color: #ffc107; color: #212529; } /* Warning */
.priority-high { background-color: #fd7e14; } /* Orange */
.priority-critical { background-color: #dc3545; } /* Danger */

/* Status Colors */
.status-new { background-color: #6c757d; } /* Secondary */
.status-open { background-color: #007bff; } /* Primary */
.status-in-progress { background-color: #ffc107; color: #212529; } /* Warning */
.status-resolved { background-color: #28a745; } /* Success */
.status-closed { background-color: #343a40; } /* Dark */

/* Actions Button */
.actions-bar {
    margin-bottom: 20px;
}
.view-switcher {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.view-toggle .button-link {
    background-color: #f1f1f1;
    color: #333;
    border: 1px solid #ddd;
}
.view-toggle .button-link.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
</style>