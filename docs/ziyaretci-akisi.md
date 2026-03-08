# Ziyaretçi Akışı

Bu belge, `urun-bilgisi/{share_token}` üzerinden gelen ziyaretçinin yaşadığı adımları açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Tek Giriş Noktası: Gate

**URL:** `GET /urun-bilgisi/{share_token}`  
**Controller:** `ProductController::gate($share_token, Request)`

### Akış Özeti

```
1. Product::where('share_token', $share_token)->firstOrFail()
2. access_mode kontrolü (Setting::get('access_mode'))
3. qr_only + session varsa → içerik göster
4. qr_only + ref=qr yoksa → qr-only view (QR gerekli mesajı)
5. link modu veya ref=qr varsa → landing (yönlendirme + geri sayım)
```

---

## access_mode Değerleri

| Değer | Anlam | Davranış |
|-------|-------|----------|
| `link` | Tüm linkler geçerli | Doğrudan landing, içerik göstermeye gidebilir |
| `qr_only` | Sadece QR ile | `?ref=qr` yoksa "QR Kod Gerekli" sayfası |

---

## Landing Sayfası (product.landing)

**View:** `resources/views/product/landing.blade.php`

### Parametreler

- `product` – Ürün modeli
- `showRedirect` – Geri sayım gösterilsin mi
- `showContent` – İçerik (description, images, videos, pdf) gösterilsin mi

### Senaryolar

1. **showRedirect=true, showContent=false (ilk giriş)**
   - 5 saniye geri sayım
   - JavaScript: `saveVisit` POST → `confirmEnter` GET → sayfa yenileme veya session ile tekrar gate

2. **showRedirect=false, showContent=true (giriş onaylanmış)**
   - Geri sayım yok, doğrudan içerik
   - Session: `product_entered_{share_token}` = true

---

## saveVisit Endpoint

**URL:** `POST /urun-bilgisi/{share_token}/kaydet`  
**Response:** `{"success": true}` (JSON)

- IP alınır (`getClientIpForGeo`)
- ip-api.com ile konum bilgisi çekilir
- `ProductVisit` kaydı oluşturulur (product_id, ip, user_agent, device_type, browser, platform, city, country, vb.)
- Hata olursa anonim kayıt veya error_message ile kayıt yapılır

Detay: [ziyaret-takibi.md](ziyaret-takibi.md)

---

## confirmEnter Endpoint

**URL:** `GET/POST /urun-bilgisi/{share_token}/onayla`

- Session: `product_entered_{share_token}` = true
- AJAX isteği ise: JSON `{"success": true}`
- Normal GET ise: `redirect()->route('product.gate', $share_token)` (session set edilmiş şekilde gate’e döner)

---

## Tam Akış Özeti

```
[Ziyaretçi] Link/QR ile urun-bilgisi/{share_token} açar
     ↓
[Gate] Product bulunur, access_mode kontrol edilir
     ↓
[Landing] showRedirect=true → 5 sn geri sayım
     ↓
[JavaScript] POST saveVisit → Ziyaret kaydedilir
     ↓
[JavaScript] GET confirmEnter → Session set edilir
     ↓
[Sayfa yenileme veya redirect] Gate tekrar çağrılır
     ↓
[Gate] session('product_entered_...') var → showContent=true
     ↓
[Landing] İçerik gösterilir (açıklama, görseller, videolar, PDF)
```

---

## İlgili Dokümanlar

- [ziyaret-takibi.md](ziyaret-takibi.md) – Ziyaret kaydı detayları
- [qr-kod.md](qr-kod.md) – QR üretimi ve URL formatı
- [ayarlar.md](ayarlar.md) – access_mode ayarı
