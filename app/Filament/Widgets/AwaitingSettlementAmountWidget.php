<?php

// تم نقل هذا الـ widget عمداً إلى:
//   app/Filament/Resources/PaymentCollectionResource/Widgets/AwaitingSettlementAmountWidget.php
//
// السبب: هذا المجلد (app/Filament/Widgets) يخضع لـ discoverWidgets() في
// AdminPanelProvider ويُضيف تلقائياً أي widget بداخله للوحة التحكم الرئيسية
// (/admin) — لكن هذا الـ widget يجب أن يظهر فقط داخل صفحة قائمة
// PaymentCollectionResource (عبر getHeaderWidgets() في ListPaymentCollections)،
// وليس على الداشبورد الرئيسي. لذلك نُقل إلى مجلد خاص بالـ Resource خارج نطاق
// discoverWidgets()، ولا يُعرَّف هنا أي كلاس عمداً (حتى لا يُكتشف مرتين).
