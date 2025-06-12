<?php // views/promotions/customer_selection.php ?>
<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>الرجاء اختيار زبون لإدارة خدمات الدعاية والعقود الخاصة به.</p>

<div class="customer-list-grid">
    <?php foreach($customers as $customer): ?>
        <a href="index.php?page=promotions&customer_id=<?php echo $customer['customer_id']; ?>" class="customer-card">
            <h3><?php echo htmlspecialchars($customer['name']); ?></h3>
            <p><?php echo htmlspecialchars($customer['customer_code']); ?></p>
        </a>
    <?php endforeach; ?>
</div>

<style>
.customer-list-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.customer-card { display: block; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #333; transition: box-shadow 0.3s, transform 0.3s; }
.customer-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-3px); }
.customer-card h3 { margin: 0 0 5px 0; color: var(--primary-color); }
.customer-card p { margin: 0; color: #777; }
</style>