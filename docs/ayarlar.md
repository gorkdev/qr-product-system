# Ayarlar

Bu belge, sistem ayarlarını ve erişim modunu açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/ayarlar` → `setting.index`
- **Controller:** `SettingController`
- **View:** `admin.settings`

---

## Mevcut Ayar: access_mode

| Değer | Açıklama |
|-------|----------|
| `link` | Herkes link ile erişebilir (varsayılan) |
| `qr_only` | Sadece QR kodla (`?ref=qr`) erişim mümkün |

### Etkileri

- **Gate:** `ProductController::isQrAccessAllowed()` bu değere göre karar verir
- **QR URL:** `qr_only` ise QR kod `...?ref=qr` ile üretilir
- **Ürün linki:** Admin panelde gösterilen link de buna göre `?ref=qr` içerebilir

---

## Setting Model

**Dosya:** `app/Models/Setting.php`

- **Tablo:** `settings` (key, value)
- `Setting::get($key, $default)` – Cache 60 sn
- `Setting::set($key, $value)` – Kaydet ve cache’i sil

---

## Form İşlemi

1. **GET /ayarlar:** Mevcut `access_mode` view’a gönderilir
2. **POST /ayarlar:** `access_mode` validate (`required|in:qr_only,link`)
3. `Setting::set('access_mode', $value)` çağrılır
4. Redirect + success mesajı

---

## View: admin.settings

- Radios: "Link ile erişim" / "Sadece QR ile erişim"
- Kaydet butonu
