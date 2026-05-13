#!/bin/bash

# ============================================================
# deploy.sh — سكريبت النشر على cPanel (SSH Terminal)
# الاستخدام: bash deploy.sh
# ============================================================

set -e  # أوقف عند أي خطأ

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${BLUE}[DEPLOY]${NC} $1"; }
success(){ echo -e "${GREEN}[✓]${NC} $1"; }
warn()   { echo -e "${YELLOW}[!]${NC} $1"; }
error()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }

# ─── التحقق من وجود .env ───────────────────────────────
if [ ! -f ".env" ]; then
    error "ملف .env غير موجود! انسخ .env.production.example وعدّله أولاً."
fi

log "بدء النشر على بيئة الإنتاج..."

# ─── 1. Maintenance Mode ──────────────────────────────
log "تفعيل وضع الصيانة..."
php artisan down --message="جارٍ التحديث، نعود خلال دقائق..." --retry=30
success "Maintenance Mode مُفعَّل"

# ─── 2. تثبيت الحزم ──────────────────────────────────
log "تثبيت حزم Composer (للإنتاج فقط)..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
success "Composer packages مثبّتة"

# ─── 3. Migrations ────────────────────────────────────
log "تشغيل Migrations..."
php artisan migrate --force
success "Migrations مكتملة"

# ─── 4. Storage Link ──────────────────────────────────
log "إنشاء Storage symlink..."
php artisan storage:link --force 2>/dev/null || warn "Storage link موجود بالفعل"

# ─── 5. Optimize ──────────────────────────────────────
log "تحسين الأداء (config + route + view + event cache)..."
php artisan optimize:clear
php artisan optimize
success "Optimize مكتمل"

# ─── 6. Permissions ───────────────────────────────────
log "ضبط الصلاحيات..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
success "Permissions مضبوطة"

# ─── 7. Disable Maintenance ───────────────────────────
log "إلغاء وضع الصيانة..."
php artisan up
success "التطبيق عاد للعمل ✓"

# ─── 8. Health Check ──────────────────────────────────
log "فحص الـ Health endpoint..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/up" 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" = "200" ]; then
    success "Health Check: ${HTTP_STATUS} ✓"
else
    warn "Health Check أعاد: ${HTTP_STATUS} — تحقق يدوياً"
fi

echo ""
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✅ النشر مكتمل بنجاح!${NC}"
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo ""
echo "  🌐 الموقع: ${APP_URL:-https://app.yourdomain.com}"
echo "  🔐 Admin:  ${APP_URL:-https://app.yourdomain.com}/admin"
echo "  📋 Logs:   storage/logs/laravel.log"
echo ""
