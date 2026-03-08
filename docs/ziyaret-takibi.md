# Ziyaret Takibi

Bu belge, ziyaret kaydı (ProductVisit) oluşturma ve konum bilgisi alma sürecini açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **Endpoint:** `POST /urun-bilgisi/{share_token}/kaydet` → `product.saveVisit`
- **Alternatif:** `POST .../kaydet-anonim` → Hata durumunda anonim kayıt

---

## Kaydedilen Alanlar

| Alan | Kaynak | Açıklama |
|------|--------|----------|
| product_id | Route | Ürün ID |
| ip_address | Request / X-Forwarded-For | İstemci IP |
| user_agent | Request | Tarayıcı bilgisi |
| device_type | user_agent parse | Mobil, Tablet, Masaüstü |
| browser | user_agent parse | Chrome, Firefox, Safari, Edge, Diğer |
| platform | user_agent parse | Windows, Apple, Android, Linux, Diğer |
| city | ip-api.com | Şehir |
| country | ip-api.com | Ülke |
| region_name | ip-api.com | Bölge |
| timezone | ip-api.com | Saat dilimi |
| isp | ip-api.com | İnternet sağlayıcı |
| lat, lon | ip-api.com | Enlem, boylam |
| visited_at | now() | Ziyaret zamanı |
| error_message | Hata durumu | API hatası vb. |
| is_anonymous | saveVisitAnonymous | Anonim kayıt flag |

---

## IP Tespiti (getClientIpForGeo)

1. `$request->ip()` – Laravel default
2. Özel/yerel IP ise (127.0.0.1, 192.168.x, 10.x, 172.16–31.x):  
   `X-Forwarded-For`, `X-Real-IP`, `CF-Connecting-IP` header’ları denenir
3. Virgülle ayrılmış listede ilk public IP alınır

---

## Konum API (ip-api.com)

- **URL:** `http://ip-api.com/json/{ip}`
- **API anahtarı:** Gerekmez (ücretsiz plan)
- **Timeout:** 5 saniye
- **Dönen alanlar:** country, regionName, city, timezone, isp, lat, lon

### Özel IP’ler

- 127.0.0.1, 192.168.x, 10.x, 172.16–31.x → API çağrılmaz, boş `location` dizisi döner

---

## User-Agent Parsing

### device_type

- `Mobile` (Tablet değilse) → Mobil
- `Tablet` veya `iPad` → Tablet
- Diğer → Masaüstü

### browser

- `Edg/` → Edge
- `Chrome` → Chrome
- `Firefox` → Firefox
- `Safari` (Chrome yok) → Safari
- Diğer → Diğer

### platform

- `Windows` → Windows
- `Mac`, `iPhone`, `iPad` → Apple
- `Android` → Android
- `Linux` → Linux
- Diğer → Diğer

---

## Hata Durumları

1. **getDetailedLocationFromIp hatası:**  
   Sadece IP, user_agent, device, browser, platform ve `error_message` ile kayıt yapılır
2. **Tüm kayıt hatası:**  
   `saveVisit` yine de `{"success": true}` döner (istek tarafında hata gizlenir)
3. **saveVisitAnonymous:**  
   İstemci tarafında konum alınamadığında kullanılabilir; `is_anonymous=true`, `ip_address=null`

---

## Model: ProductVisit

**Dosya:** `app/Models/ProductVisit.php`

- `fillable`: Tüm yukarıdaki alanlar
- `casts`: `visited_at` datetime, `is_anonymous` boolean
- `product()`: `belongsTo(Product::class)`

---

## İlgili Dokümanlar

- [ziyaretci-akisi.md](ziyaretci-akisi.md) – Tam ziyaret akışı
- [ziyaret-loglari.md](ziyaret-loglari.md) – Admin ziyaret listesi
- [veritabani.md](veritabani.md) – product_visits tablosu
