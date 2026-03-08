# Veritabanı Yapısı

Bu belge, migration’lar ve tablo yapılarını açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Migration Sırası

| Dosya | Tablo / İşlem |
|-------|----------------|
| 0001_01_01_000000_create_users_table | users |
| 0001_01_01_000001_create_cache_table | cache |
| 0001_01_01_000002_create_jobs_table | jobs |
| 2026_03_08_123342_create_products_table | products |
| 2026_03_08_180000_add_share_token_to_products_table | products.share_token |
| 2026_03_08_190000_create_product_visits_table | product_visits |
| 2026_03_08_200000_add_location_to_product_visits | city, country, region_name, timezone, isp, lat, lon |
| 2026_03_08_210000_add_extended_fields_to_product_visits | error_message, is_anonymous |
| 2026_03_08_220000_create_settings_table | settings |
| 2026_03_08_230000_regenerate_qr_codes_with_ref | (muhtemelen artisan command) |

---

## products Tablosu

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | bigint | PK |
| uuid | uuid | unique, route key |
| name | string | Ürün adı |
| description | text | Açıklama |
| images | json | Görsel URL listesi |
| videos | json | YouTube URL listesi |
| pdf_path | string | PDF dosya yolu |
| share_token | string | Paylaşım token (64 karakter) |
| created_at | timestamp | |
| updated_at | timestamp | |

**Model:** `App\Models\Product`  
** booted:** creating → uuid, share_token; deleting → storage silme

---

## product_visits Tablosu

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | bigint | PK |
| product_id | bigint | FK → products |
| ip_address | string(45) | |
| user_agent | text | |
| device_type | string(64) | Mobil, Tablet, Masaüstü |
| browser | string(128) | |
| platform | string(64) | |
| city | string | ip-api |
| country | string | ip-api |
| region_name | string | ip-api |
| timezone | string | ip-api |
| isp | string | ip-api |
| lat, lon | decimal | ip-api |
| visited_at | timestamp | |
| error_message | text | Hata durumu |
| is_anonymous | boolean | Anonim kayıt |
| created_at, updated_at | timestamp | |

**Model:** `App\Models\ProductVisit`  
**İlişki:** product_id → Product

---

## settings Tablosu

| Sütun | Tip |
|-------|-----|
| id | bigint |
| key | string |
| value | text |
| created_at, updated_at | timestamp |

**Model:** `App\Models\Setting`  
**Kayıt:** key-value, Cache ile 60 sn TTL

---

## Varsayılan Bağlantı

- **SQLite:** `database/database.sqlite`
- **.env:** `DB_CONNECTION`, `DB_DATABASE` vb.
