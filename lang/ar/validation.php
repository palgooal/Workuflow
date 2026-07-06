<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | ملف الترجمة العربية الرسمي لرسائل التحقق (Validation) في Laravel.
    | هذا الملف كان مفقوداً بالكامل من المشروع رغم أن APP_LOCALE=ar، ما كان
    | يجعل Laravel يطبع مفتاح الترجمة الخام (مثل "validation.required") بدل
    | رسالة عربية مفهومة كلما فشل التحقق من حقل في أي نموذج بالمنصة.
    |
    */

    'accepted'             => 'يجب قبول :attribute.',
    'accepted_if'          => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url'           => ':attribute ليس رابطاً صحيحاً.',
    'after'                => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'after_or_equal'       => 'يجب أن يكون :attribute تاريخاً بعد أو يساوي :date.',
    'alpha'                => 'يجب أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash'           => 'يجب أن يحتوي :attribute على أحرف وأرقام وشرطات فقط.',
    'alpha_num'            => 'يجب أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array'                => 'يجب أن يكون :attribute مصفوفة.',
    'ascii'                => 'يجب أن يحتوي :attribute على أحرف ورموز أحادية البايت فقط.',
    'before'               => 'يجب أن يكون :attribute تاريخاً قبل :date.',
    'before_or_equal'      => 'يجب أن يكون :attribute تاريخاً قبل أو يساوي :date.',
    'between'              => [
        'array'   => 'يجب أن يحتوي :attribute على عدد عناصر بين :min و :max.',
        'file'    => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'string'  => 'يجب أن يحتوي :attribute على عدد أحرف بين :min و :max.',
    ],
    'boolean'              => 'يجب أن تكون قيمة :attribute إما صحيحة أو خاطئة.',
    'can'                  => ':attribute يحتوي على قيمة غير مسموح بها.',
    'confirmed'            => 'تأكيد :attribute غير مطابق.',
    'contains'             => ':attribute يفتقد قيمة مطلوبة.',
    'current_password'     => 'كلمة المرور غير صحيحة.',
    'date'                 => ':attribute ليس تاريخاً صحيحاً.',
    'date_equals'          => 'يجب أن يكون :attribute تاريخاً يساوي :date.',
    'date_format'          => ':attribute لا يطابق التنسيق :format.',
    'decimal'              => 'يجب أن يحتوي :attribute على :decimal رقم عشري.',
    'declined'             => 'يجب رفض :attribute.',
    'declined_if'          => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different'            => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits'               => 'يجب أن يتكون :attribute من :digits أرقام.',
    'digits_between'       => 'يجب أن يتكون :attribute من رقم بين :min و :max.',
    'dimensions'           => 'أبعاد الصورة :attribute غير صحيحة.',
    'distinct'             => 'حقل :attribute يحتوي على قيمة مكررة.',
    'doesnt_end_with'      => 'يجب ألا ينتهي :attribute بأحد القيم التالية: :values.',
    'doesnt_start_with'    => 'يجب ألا يبدأ :attribute بأحد القيم التالية: :values.',
    'email'                => 'يجب أن يكون :attribute بريداً إلكترونياً صحيحاً.',
    'ends_with'            => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum'                 => 'القيمة المختارة لـ :attribute غير صحيحة.',
    'exists'               => ':attribute المحدد غير موجود.',
    'extensions'           => 'يجب أن يكون لملف :attribute أحد الامتدادات التالية: :values.',
    'file'                 => 'يجب أن يكون :attribute ملفاً.',
    'filled'               => 'حقل :attribute مطلوب.',
    'gt'                   => [
        'array'   => 'يجب أن يحتوي :attribute على عناصر أكثر من :value.',
        'file'    => 'يجب أن يكون حجم :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        'string'  => 'يجب أن يحتوي :attribute على أحرف أكثر من :value.',
    ],
    'gte'                  => [
        'array'   => 'يجب أن يحتوي :attribute على :value عنصر على الأقل.',
        'file'    => 'يجب أن يكون حجم :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من أو تساوي :value.',
        'string'  => 'يجب أن يحتوي :attribute على :value حرف على الأقل.',
    ],
    'hex_color'            => 'يجب أن يكون :attribute لوناً سداسياً صحيحاً.',
    'image'                => 'يجب أن يكون :attribute صورة.',
    'in'                   => 'القيمة المختارة لـ :attribute غير صحيحة.',
    'in_array'             => 'حقل :attribute غير موجود في :other.',
    'integer'              => 'يجب أن يكون :attribute عدداً صحيحاً.',
    'ip'                   => 'يجب أن يكون :attribute عنوان IP صحيحاً.',
    'ipv4'                 => 'يجب أن يكون :attribute عنوان IPv4 صحيحاً.',
    'ipv6'                 => 'يجب أن يكون :attribute عنوان IPv6 صحيحاً.',
    'json'                 => 'يجب أن يكون :attribute نص JSON صحيحاً.',
    'list'                 => 'يجب أن يكون :attribute قائمة.',
    'lowercase'            => 'يجب أن يكون :attribute بأحرف صغيرة.',
    'lt'                   => [
        'array'   => 'يجب أن يحتوي :attribute على عناصر أقل من :value.',
        'file'    => 'يجب أن يكون حجم :attribute أصغر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من :value.',
        'string'  => 'يجب أن يحتوي :attribute على أحرف أقل من :value.',
    ],
    'lte'                  => [
        'array'   => 'يجب ألا يحتوي :attribute على أكثر من :value عنصر.',
        'file'    => 'يجب أن يكون حجم :attribute أصغر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من أو تساوي :value.',
        'string'  => 'يجب ألا يحتوي :attribute على أكثر من :value حرف.',
    ],
    'mac_address'          => 'يجب أن يكون :attribute عنوان MAC صحيحاً.',
    'max'                  => [
        'array'   => 'يجب ألا يحتوي :attribute على أكثر من :max عنصر.',
        'file'    => 'يجب ألا يتجاوز حجم :attribute عن :max كيلوبايت.',
        'numeric' => 'يجب ألا تكون قيمة :attribute أكبر من :max.',
        'string'  => 'يجب ألا يحتوي :attribute على أكثر من :max حرف.',
    ],
    'max_digits'           => 'يجب ألا يحتوي :attribute على أكثر من :max رقم.',
    'mimes'                => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'mimetypes'            => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'min'                  => [
        'array'   => 'يجب أن يحتوي :attribute على الأقل على :min عنصر.',
        'file'    => 'يجب أن يكون حجم :attribute على الأقل :min كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute على الأقل :min.',
        'string'  => 'يجب أن يحتوي :attribute على الأقل على :min حرف.',
    ],
    'min_digits'           => 'يجب أن يحتوي :attribute على الأقل على :min رقم.',
    'missing'              => 'يجب أن يكون حقل :attribute مفقوداً.',
    'missing_if'           => 'يجب أن يكون حقل :attribute مفقوداً عندما يكون :other هو :value.',
    'missing_unless'       => 'يجب أن يكون حقل :attribute مفقوداً إلا إذا كان :other هو :value.',
    'missing_with'         => 'يجب أن يكون حقل :attribute مفقوداً عندما يكون :values موجوداً.',
    'missing_with_all'     => 'يجب أن يكون حقل :attribute مفقوداً عندما تكون :values موجودة.',
    'multiple_of'          => 'يجب أن تكون قيمة :attribute من مضاعفات :value.',
    'not_in'               => 'القيمة المختارة لـ :attribute غير صحيحة.',
    'not_regex'            => 'صيغة :attribute غير صحيحة.',
    'numeric'              => 'يجب أن يكون :attribute رقماً.',
    'password'             => [
        'letters'       => 'يجب أن يحتوي :attribute على حرف واحد على الأقل.',
        'mixed'         => 'يجب أن يحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers'       => 'يجب أن يحتوي :attribute على رقم واحد على الأقل.',
        'symbols'       => 'يجب أن يحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'القيمة المُدخلة لـ :attribute ظهرت في تسريب بيانات معروف. يرجى اختيار :attribute مختلف.',
    ],
    'present'              => 'حقل :attribute يجب أن يكون موجوداً.',
    'present_if'           => 'حقل :attribute يجب أن يكون موجوداً عندما يكون :other هو :value.',
    'present_unless'       => 'حقل :attribute يجب أن يكون موجوداً إلا إذا كان :other هو :value.',
    'present_with'         => 'حقل :attribute يجب أن يكون موجوداً عندما يكون :values موجوداً.',
    'present_with_all'     => 'حقل :attribute يجب أن يكون موجوداً عندما تكون :values موجودة.',
    'prohibited'           => 'حقل :attribute محظور.',
    'prohibited_if'        => 'حقل :attribute محظور عندما يكون :other هو :value.',
    'prohibited_unless'    => 'حقل :attribute محظور إلا إذا كان :other ضمن :values.',
    'prohibits'            => 'حقل :attribute يمنع وجود :other.',
    'regex'                => 'صيغة :attribute غير صحيحة.',
    'required'             => 'حقل :attribute مطلوب.',
    'required_array_keys'  => 'يجب أن يحتوي حقل :attribute على قيم لـ: :values.',
    'required_if'          => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => 'حقل :attribute مطلوب عندما يتم قبول :other.',
    'required_if_declined' => 'حقل :attribute مطلوب عندما يتم رفض :other.',
    'required_unless'      => 'حقل :attribute مطلوب إلا إذا كان :other ضمن :values.',
    'required_with'        => 'حقل :attribute مطلوب عندما يكون :values موجوداً.',
    'required_with_all'    => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without'     => 'حقل :attribute مطلوب عندما لا يكون :values موجوداً.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same'                 => 'يجب أن يتطابق :attribute و :other.',
    'size'                 => [
        'array'   => 'يجب أن يحتوي :attribute على :size عنصر.',
        'file'    => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute :size.',
        'string'  => 'يجب أن يحتوي :attribute على :size حرف.',
    ],
    'starts_with'          => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string'               => 'يجب أن يكون :attribute نصاً.',
    'timezone'             => 'يجب أن يكون :attribute منطقة زمنية صحيحة.',
    'unique'               => ':attribute مستخدم من قبل.',
    'uploaded'             => 'فشل رفع :attribute.',
    'uppercase'            => 'يجب أن يكون :attribute بأحرف كبيرة.',
    'url'                  => 'يجب أن يكون :attribute رابطاً صحيحاً.',
    'ulid'                 => 'يجب أن يكون :attribute من نوع ULID صحيح.',
    'uuid'                 => 'يجب أن يكون :attribute من نوع UUID صحيح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | يمكنك هنا إضافة رسائل مخصّصة لقاعدة/حقل معيّن بصيغة "field.rule".
    | معظم النماذج بالمنصة تُعرِّف رسائلها الخاصة عبر messages() في الـ
    | FormRequest، وهذا القسم يبقى احتياطياً للنماذج التي لا تفعل ذلك.
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | تُستخدم لاستبدال اسم الحقل التقني (مثل client_id) باسم عربي مفهوم
    | داخل الرسالة (مثل "العميل") في أي نموذج لا يُعرّف attributes() خاصة به.
    |
    */

    'attributes' => [
        'name'            => 'الاسم',
        'email'           => 'البريد الإلكتروني',
        'password'        => 'كلمة المرور',
        'phone'           => 'رقم الهاتف',
        'currency'        => 'العملة',
        'client_id'       => 'العميل',
        'project_id'      => 'المشروع',
        'title'           => 'العنوان',
        'issue_date'      => 'تاريخ الإصدار',
        'valid_until'     => 'تاريخ انتهاء الصلاحية',
        'due_date'        => 'تاريخ الاستحقاق',
        'tax_rate'        => 'نسبة الضريبة',
        'discount'        => 'الخصم',
        'discount_type'   => 'نوع الخصم',
        'notes'           => 'الملاحظات',
        'terms'           => 'الشروط والأحكام',
        'items'           => 'البنود',
        'items.*.description' => 'وصف البند',
        'items.*.quantity'     => 'كمية البند',
        'items.*.unit_price'   => 'سعر وحدة البند',
        'description'     => 'الوصف',
        'color'           => 'اللون',
        'type'            => 'النوع',
        'status'          => 'الحالة',
        'contract_value'  => 'قيمة العقد',
        'services'        => 'الخدمات',
        'services.*.service_id'        => 'الخدمة',
        'services.*.amount'            => 'قيمة الخدمة',
        'services.*.notes'             => 'ملاحظات الخدمة',
        'services.*.target_margin_pct' => 'نسبة الهامش المستهدف',
        'services.*.members'                  => 'منفذو الخدمة',
        'services.*.members.*.team_member_id' => 'منفذ الخدمة',
        'services.*.members.*.team_cost'      => 'تكلفة المنفذ',
        'amount'          => 'المبلغ',
        'category_id'     => 'التصنيف',
        'wallet_id'       => 'الصندوق',
        'transaction_date'=> 'تاريخ الحركة',
    ],

];
