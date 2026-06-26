# SECURITY-02-PHASE-P4-USERRESOURCE-PHONE-REPORT
**Sprint:** SECURITY-02 — Phone Registration / Phase P4: Filament Admin UserResource  
**Date:** 2026-06-26  
**Status:** ✅ Implemented

---

## Files Modified

**Total files changed: 1**

| File | Change |
|---|---|
| `app/Filament/Resources/UserResource.php` | Added phone form field, table column, two filters, WhatsApp action |

No new files. No migrations. No packages.

---

## Form Field

Added to `المعلومات الأساسية` section, between `email` and `password`:

```php
Forms\Components\TextInput::make('phone')
    ->label('رقم الجوال')
    ->tel()
    ->placeholder('+970599123456')
    ->maxLength(30)
    ->nullable()
    ->unique(ignoreRecord: true)
    ->regex('/^\+[1-9]\d{5,14}$/')
    ->validationMessages([
        'regex'  => 'صيغة رقم الجوال غير صحيحة. مثال: +970599123456',
        'unique' => 'رقم الجوال هذا مستخدم من قِبل حساب آخر.',
    ]),
```

**Key decisions:**
- `->nullable()` — admins may create or edit users without a phone; the field is not mandatory in admin context unlike the self-registration form.
- `->unique(ignoreRecord: true)` — Filament's shorthand; resolves to `Rule::unique('users', 'phone')->ignore($record->id)` automatically.
- `->tel()` — renders `type="tel"` for mobile keyboards.
- Arabic messages via `->validationMessages()` — Filament 3 equivalent of `messages()` on a FormRequest.

---

## Table Column

Added after the `email` column:

```php
Tables\Columns\TextColumn::make('phone')
    ->label('الجوال')
    ->searchable()
    ->copyable()
    ->copyMessage('تم نسخ رقم الجوال')
    ->placeholder('—')
    ->toggleable(isToggledHiddenByDefault: false),
```

- Visible by default (not hidden) — phone is a primary contact identifier in admin.
- `->searchable()` — allows finding a user by their phone number in the global table search.
- `->copyable()` — one-click copy to clipboard with Arabic confirmation toast.
- `->placeholder('—')` — shows an em dash for users with `phone = NULL` instead of blank cell.

---

## Filters

Added after `no_activity` filter, under a `// ── Phone filters ──` comment:

```php
Tables\Filters\Filter::make('has_phone')
    ->label('لديه رقم جوال')
    ->query(fn (Builder $query) => $query->whereNotNull('phone'))
    ->toggle(),

Tables\Filters\Filter::make('missing_phone')
    ->label('بدون رقم جوال')
    ->query(fn (Builder $query) => $query->whereNull('phone'))
    ->toggle(),
```

Both use the typed `fn (Builder $query)` signature (Sprint-03 lesson: prevents BindingResolutionException in Filament filter closures).

**Use cases:**
- `has_phone` — see all users with a registered phone, e.g. to spot-check data quality.
- `missing_phone` — identify users who registered before P2 was deployed and have no phone on file, or users who bypassed the form.

---

## WhatsApp Action

Added as the second action (after EditAction, before resendVerification):

```php
Tables\Actions\Action::make('whatsapp')
    ->label('واتساب')
    ->icon('heroicon-o-chat-bubble-left-ellipsis')
    ->color('success')
    ->url(fn (User $record): string => 'https://wa.me/' . ltrim($record->phone ?? '', '+'))
    ->openUrlInNewTab()
    ->visible(fn (User $record): bool => filled($record->phone)),
```

**How it works:**
- `ltrim($record->phone, '+')` strips the leading `+` from the stored E.164 number.
  - `+970599123456` → `wa.me/970599123456` ✅
- `->openUrlInNewTab()` — opens WhatsApp Web or WhatsApp app picker in a new browser tab without navigating away from the admin panel.
- `->visible(fn ...)` — the button does not render at all for users with `phone = NULL`. No broken `wa.me/` link.
- `heroicon-o-chat-bubble-left-ellipsis` — available in Heroicons v2 (used by Filament 3.x).
- Color `success` (green) — visually distinct from the edit (gray) and resend-verification (info/blue) actions.

**URL format validation:**
| Stored `phone` | Rendered URL |
|---|---|
| `+970599123456` | `https://wa.me/970599123456` |
| `+966501234567` | `https://wa.me/966501234567` |
| `+447911123456` | `https://wa.me/447911123456` |
| `NULL` | *(button hidden)* |

---

## Validation

| Context | Rule | Behavior |
|---|---|---|
| Admin create | `nullable` + `regex` + `unique(ignoreRecord: true)` | Phone optional; if provided must be valid E.164 and unique |
| Admin edit | Same | `ignoreRecord: true` means editing your own phone doesn't fail uniqueness |
| Registration (P2) | `required` + `regex` + `unique:users,phone` | Phone mandatory at registration |
| Settings (P3) | `required` + `regex` + `Rule::unique->ignore(id)` | Phone mandatory when saving profile |

The admin form intentionally uses `nullable()` — the admin may need to create stub accounts or fix records without forcing a phone.

---

## Regression Risks

| Risk | Severity | Assessment |
|---|---|---|
| `heroicon-o-chat-bubble-left-ellipsis` not available in installed Heroicons version | 🟡 Low | Available in Heroicons v2 which Filament 3.x ships with. If it errors, replace with `heroicon-o-phone` which is definitively available. |
| `ltrim($record->phone ?? '', '+')` on NULL | 🟢 None | `visible()` check runs first; action does not render if `phone` is NULL. The `?? ''` is a safety fallback only. |
| `unique(ignoreRecord: true)` in Filament form vs. `Rule::unique()->ignore()` in FormRequest | 🟢 None | Filament resolves `ignoreRecord: true` to the correct ignore logic automatically. Distinct from the FormRequest pattern but functionally identical. |
| New phone filters conflict with existing `email_verified`/`no_activity` filters | 🟢 None | Filament toggle filters are independent; all use typed `Builder $query`. |
| Phone column `->searchable()` slows large tables | 🟢 Low | MySQL UNIQUE index on `phone` (added in P1 migration) means the search query hits an indexed column. No full-table scan. |
| Admin sets phone to value that conflicts with registration-side unique index | 🟢 None | The `unique(ignoreRecord: true)` constraint catches it; Filament shows the Arabic validation message. |

---

## Git Commit Message

```
feat(admin): add phone to UserResource — column, form, filters, WhatsApp action (SECURITY-02 / Phase P4)

UserResource.php:
- Form: TextInput phone (tel, nullable, unique ignoreRecord, E.164 regex, Arabic messages)
- Table: TextColumn phone (searchable, copyable, placeholder, visible by default)
- Filters: has_phone (whereNotNull) + missing_phone (whereNull) — both toggle
- Action: whatsapp (visible if phone filled, opens wa.me/{phone without +} in new tab)

No new files. No migrations. No packages.

Refs: SECURITY-02-AUDIT-REPORT.md, SECURITY-02-PHASE-P1-REPORT.md,
      SECURITY-02-PHASE-P2-REPORT.md, SECURITY-02-PHASE-P3-SETTINGS-PHONE-REPORT.md
```
