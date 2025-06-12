<?php // views/reports/dashboard.php ?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p>اختر أحد التقارير المتاحة من القائمة أدناه لعرضه.</p>

<div class="reports-grid">
    <a href="index.php?page=reports&type=top_selling_items" class="report-card">
        <i class="fas fa-trophy"></i>
        <h3>الأصناف الأكثر مبيعًا</h3>
        <p>عرض قائمة بالأصناف الأعلى مبيعًا حسب الكمية أو القيمة.</p>
    </a>
    <a href="index.php?page=reports&type=stagnant_items" class="report-card">
        <i class="fas fa-box-open"></i>
        <h3>الأصناف الراكدة</h3>
        <p>عرض قائمة بالأصناف التي لم تحقق أي مبيعات خلال فترة محددة.</p>
    </a>
    
    <!-- بطاقات التقارير الأخرى ستضاف هنا -->
    <a href="index.php?page=reports&type=customer_purchase_analysis" class="report-card">
        <i class="fas fa-users"></i>
        <h3>مبيعات نقاط البيع للاصناف</h3>
        <p>يستعرض مبيعات الاصناف لنقاط البيع</p>
     </a>

    <a href="index.php?page=reports&type=product_distribution" class="report-card">
        <i class="fas fa-map-marked-alt"></i>
        <h3>مبيعات الاصناف موزعه على نقاط البيع</h3>
        <p>يوضح عدد نقاط البيع التي قامت بشراء منتج محدد</p>
    </a>

     <a href="index.php?page=reports&type=item_target_performance" class="report-card">
        <i class="fas fa-bullseye"></i>
        <h3>تحقيق اهداف المبيعات</h3>
        <p>مقارنة المبيعات الكمية مع الاهداف لكل صنف</p>
    </a>

     <a href="index.php?page=reports&type=value_target_performance" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>مقارنة الاهداف مع المبيعات الرقمية</h3>
        <p>مقارنة مبيعات المندوبين مع الاهداف الشهرية</p>
    </a>

         <a href="index.php?page=reports&type=customer_item_yoy_comparison" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>مقارنة مبيعات زبائن سنوي</h3>
        <p>مقارنة مبيعات كل زبون من الاصناف على مستوى السنوات</p>
    </a>

         <a href="index.php?page=reports&type=rep_effectiveness" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير فعالية المندوب</h3>
        <p>قياس فعالية اداء المندوب</p>
    </a>
         <a href="index.php?page=reports&type=contractual_targets" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير الاهداف التعاقدية</h3>
        <p>متابعة الاهداف التعاقدية للتجار</p>
    </a>

        </a>
         <a href="index.php?page=reports&type=sales_vs_collection" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير نسبة المبيعات من التحصيل </h3>
        <p>مقارنة التحصيل الشهري بالمبيعات الشهرية</p>
    </a>

        </a>
         <a href="index.php?page=reports&type=trend_analysis" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير الاهداف التعاقدية </h3>
        <p>متابعة الاهداف التعاقدية للتجار بشكل صور بيانية</p>
    </a>

            </a>
         <a href="index.php?page=reports&type=sales_mix" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير مزيج المبيعات</h3>
        <p>فهم المنتجات أو عائلات المنتجات التي تشكل الجزء الأكبر من مبيعات كل مندوب أو منطقة. يساعد هذا في معرفة ما إذا كان المندوب يركز على المنتجات عالية الهامش أم لا، أو إذا كانت هناك عائلة منتجات مهملة في منطقة معينة.</p>
    </a>
        </a>
         <a href="index.php?page=reports&type=lost_customers" class="report-card">
        <i class="fas fa-chart-line"></i>
        <h3>تقرير الزبائن المفقودة</h3>
        <p> تقرير الزبائن الذين لم يتم بيعهم</p>
    </a>
</div>

<style>
.reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;}
.report-card { display: block; padding: 25px; border: 1px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; text-align: center; transition: all 0.3s; }
.report-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.report-card i { font-size: 2.5em; color: var(--primary-color); margin-bottom: 15px; }
.report-card h3 { margin: 10px 0; }
.report-card p { font-size: 0.9em; color: #666; }
.report-card.coming-soon { background-color: #f8f9fa; opacity: 0.7; cursor: not-allowed; }
.report-card.coming-soon:hover { transform: none; box-shadow: none; }
</style>