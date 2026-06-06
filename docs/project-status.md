# توثيق — حالة المشروع (Project Status)
## دراهم | مال وأعمال

> تاريخ التوثيق: يونيو 2026
> الحالة: ✅ مكتمل

---

## المشكلة

`is_active` boolean بسيط — نشط أو غير نشط. لا يعبّر عن واقع المشاريع:
- مشروع انتهى وتم التسليم ≠ مشروع متوقف مؤقتاً
- المستخدم لا يمكنه التمييز بين "مكتمل" و"ملغي" و"متوقف"
- كل المشاريع تظهر "نشط" افتراضياً حتى المنتهية

## الضمان المهم

**التقارير والأرباح والخسائر لا تتأثر بهذا التغيير.**
السبب: الحسابات المالية مبنية على المعاملات (`transactions`) المرتبطة بـ `project_id`،
وليس على حالة المشروع. مشروع "مكتمل" معاملاته تظل في التقارير كما هي.

---

## الحل

استبدال `is_active` (boolean) بـ `status` (enum) مع 4 قيم:

| القيمة | العرض | الوصف |
|--------|-------|-------|
| `active` | 🟢 نشط | يُعمل عليه الآن |
| `completed` | ✅ مكتمل | تم التسليم والإغلاق |
| `on_hold` | ⏸ متوقف | مؤجل مؤقتاً |
| `cancelled` | ❌ ملغي | تم الإلغاء |

---

## Enum: ProjectStatus

**الملف:** `app/Support/Enums/ProjectStatus.php`

```php
enum ProjectStatus: string {
    case Active    = 'active';
    case Completed = 'completed';
    case OnHold    = 'on_hold';
    case Cancelled = 'cancelled';
}
```

**Methods:** `label()`, `color()`, `icon()`, `isActive(): bool`

---

## التغييرات في الـ Schema

```sql
-- قبل
projects.is_active  BOOLEAN DEFAULT 1

-- بعد
projects.status  ENUM('active','completed','on_hold','cancelled') DEFAULT 'active'
```

**الترحيل:**
- `is_active = 1` → `status = 'active'`
- `is_active = 0` → `status = 'on_hold'`

---

## التوافق مع الكود القديم

أُضيف accessor محسوب على الـ Model:
```php
public function getIsActiveAttribute(): bool
{
    return $this->status === ProjectStatus::Active;
}
```
هذا يجعل `$project->is_active` تعمل في أي مكان بقي فيه الكود القديم.

---

## الملفات المعدّلة

| الملف | التغيير |
|-------|---------|
| `migration 2026_06_06_000004` | إضافة `status` + ترحيل + حذف `is_active` |
| `app/Support/Enums/ProjectStatus.php` | Enum جديد |
| `app/Models/Project.php` | cast + fillable + scope + accessor |
| `app/Modules/Projects/DTOs/ProjectData.php` | `bool $is_active` → `ProjectStatus $status` |
| `app/Modules/Projects/Actions/CreateProjectAction.php` | `$data->status` |
| `app/Modules/Projects/Actions/UpdateProjectAction.php` | `$data->status` |
| `app/Http/Requests/Projects/StoreProjectRequest.php` | validate status enum |
| `app/Http/Requests/Projects/UpdateProjectRequest.php` | validate status enum |
| `app/Http/Controllers/ProjectController.php` | ترتيب قائمة المشاريع |
| `app/Modules/Projects/Services/ProjectFinancialService.php` | عدّ المشاريع النشطة |
| `resources/views/projects/_form.blade.php` | toggle → status selector |
| `resources/views/projects/_card.blade.php` | badge الحالة |
| `resources/views/projects/show.blade.php` | badge الحالة |

---

## ترتيب قائمة المشاريع بعد التغيير

```
1. نشط (active)     ← أولاً
2. مكتمل (completed) ← ثانياً
3. متوقف (on_hold)  ← ثالثاً
4. ملغي (cancelled) ← أخيراً
```
