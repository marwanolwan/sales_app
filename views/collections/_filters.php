<?php // views/collections/_filters.php ?>
<div class="filter-bar card">
    <form action="index.php" method="GET">
        <input type="hidden" name="page" value="collections">
        <div class="form-row">
            <div class="form-group">
                <label>السنة:</label>
                <select name="year" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($filter_year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>الشهر:</label>
                <select name="month" onchange="this.form.submit()">
                    <?php foreach($months_map as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo ($filter_month == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>