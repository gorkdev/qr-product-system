# Git ve Versiyon Kontrolü

Bu belge, projenin Git kullanımını ve branch/commit kurallarını açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Depo Bilgisi

- **GitHub:** https://github.com/gorkdev/qr-product-system
- **Ana branch:** master (varsayılan)

---

## Clone

```bash
git clone https://github.com/gorkdev/qr-product-system.git
cd qr-product-system
```

---

## .gitignore

Laravel standart .gitignore kullanılır. Örnek hariç tutulanlar:

- `/vendor/`
- `/node_modules/`
- `.env`
- `/storage/*.key`
- `database/database.sqlite` (opsiyonel, projeye göre)
- `/public/storage` (symlink)
- `.phpunit.cache/`
- `npm-debug.log`
- `/.idea`, `/.vscode` vb.

---

## Commit Önerileri

- Anlamlı mesajlar: "feat: ürün listesine arama eklendi"
- Conventional Commits: `feat:`, `fix:`, `docs:`, `chore:`, `test:`

---

## Push

```bash
git add .
git commit -m "feat: yeni özellik"
git push origin master
```

---

## Yeni Branch

```bash
git checkout -b feature/yeni-ozellik
# değişiklikler...
git add .
git commit -m "feat: yeni özellik"
git push -u origin feature/yeni-ozellik
```
