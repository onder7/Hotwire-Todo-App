# 🔥 Hotwire Todo App

 Hotwire PHP Todo uygulaması - **Hotwire (Turbo + Stimulus)** ile SPA benzeri deneyim, JSON dosya depolama ile veritabanı gerektirmez!

## ✨ Özellikler

### 🚀 Modern Frontend
- **Hotwire Stack**: Turbo + Stimulus ile SPA benzeri deneyim
- **AJAX işlemler** - Sayfa yenilenmeden tüm işlemler
- **Real-time güncellemeler** - Anında görsel geri bildirim
- **Responsive tasarım** - Mobil ve masaüstü uyumlu

### 💾 Akıllı Depolama
- **JSON dosya sistemi** - MySQL/veritabanı gerektirmez
- **File locking** - Eşzamanlı yazma koruması
- **Duplicate request protection** - Çift işlem önleme
- **Backup & Export** - JSON ve CSV formatında

### ⌨️ Klavye Kısayolları
- **Ctrl + Enter**: Hızlı görev ekleme
- **Double-click**: Görev düzenleme
- **Enter**: Düzenlemeyi kaydet
- **Escape**: Düzenlemeyi iptal / Input'a odaklan
- **Ctrl + /**: Yardım menüsü

### 🎨 UX/UI Detayları
- **Karakter sayacı** - 100 karakter limiti
- **Animasyonlar** - Smooth geçişler ve hover efektleri
- **Visual feedback** - Button states ve loading durumları
- **Time stamps** - "2 dakika önce" formatında zaman gösterimi
- **Stats dashboard** - Toplam/tamamlanan/bekleyen görev sayıları

## 🛠️ Kurulum

### Gereksinimler
- PHP 7.4+ (PHP 8.0+ önerilir)
- Web sunucu (Apache/Nginx)
- Yazma izinleri (JSON dosyası için)

### Hızlı Başlangıç

```bash
# Repository'yi klonla
git clone https://github.com/yourusername/hotwire-todo-app.git
cd hotwire-todo-app

# Web sunucuda çalıştır
php -S localhost:8000

# Tarayıcıda aç
http://localhost:8000
```

### Apache/Nginx Kurulumu

Apache için `.htaccess` (opsiyonel):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## 📁 Dosya Yapısı

```
hotwire-todo-app/
├── index.php          # Ana uygulama dosyası
├── style.css          # CSS stilleri ve animasyonlar
├── script.js          # Hotwire (Turbo + Stimulus) JS
├── todos.json         # JSON veri depolama (otomatik oluşur)
├── README.md          # Bu dosya
└── .gitignore         # Git ignore kuralları
```

## 🎯 Kullanım

### Temel İşlemler
1. **Görev Ekleme**: Üstteki input'a yazın ve Enter/Ekle butonuna basın
2. **Görev Tamamlama**: ✅ Tamamla butonuna tıklayın
3. **Görev Düzenleme**: Görev metnine çift tıklayın
4. **Görev Silme**: 🗑️ Sil butonuna tıklayın

### Gelişmiş Özellikler
- **Toplu İşlemler**: Tamamlananları temizle butonu
- **Veri Yedekleme**: 💾 Yedekle butonu ile JSON export
- **CSV Export**: 📊 CSV İndir butonu ile Excel uyumlu format
- **Klavye Navigation**: Ctrl+/ ile kısayolları görün

## 🔧 Teknik Detaylar

### Backend (PHP)
```php
class TodoStorage {
    // JSON dosya depolama sistemi
    // File locking ile güvenli yazma
    // Duplicate request protection
    // CRUD operasyonları
}
```

### Frontend (JavaScript)
```javascript
// Hotwire Stack
import '@hotwired/turbo'
import '@hotwired/stimulus'

// Stimulus Controllers:
// - FormController: Form işlemleri ve validasyon
// - TodoItemController: Görev item davranışları
// - StatsController: İstatistik güncellemeleri
// - KeyboardController: Klavye kısayolları
```

### CSS Framework
- **Utility-first** yaklaşımı
- **CSS Grid & Flexbox** layout
- **CSS Animations** - keyframes ile smooth geçişler
- **Responsive** - mobile-first design

## 🚦 API Endpoints

```
GET  /?action=index           # Ana sayfa
POST /?action=add             # Yeni görev ekleme
GET  /?action=toggle&id=X     # Görev durumu değiştirme
GET  /?action=delete&id=X     # Görev silme
POST /?action=edit            # Görev düzenleme
GET  /?action=clear_completed # Tamamlananları temizle
GET  /?action=backup          # JSON backup indirme
GET  /?action=export          # CSV export
```

## 🎨 Özelleştirme

### Tema Değişikliği
`style.css` dosyasında renk değişkenleri:
```css
:root {
    --primary-gradient: linear-gradient(135deg, #667eea, #764ba2);
    --danger-gradient: linear-gradient(135deg, #ff6b6b, #ee5a24);
    --success-gradient: linear-gradient(135deg, #2ecc71, #27ae60);
}
```

### JavaScript Davranışları
Stimulus controller'ları `script.js` dosyasında özelleştirilebilir:
```javascript
window.Stimulus.register("custom", class extends Controller {
    // Özel davranışlar
})
```

## 🐛 Hata Ayıklama

### Debug Modu
Browser developer tools'da console log'ları:
```javascript
console.log('🔄 Toggle: ID=todo_123, Current=pending')
console.log('📡 AJAX isteği başladı: /index.php?action=toggle&id=123')
```

### PHP Error Logs
Server error log'larında toggle işlem detayları:
```
[26-Jun-2025 08:42:13 UTC] Toggle Todo - ID: todo_123, Found: Yes, Saved: Yes
[26-Jun-2025 08:42:13 UTC] Toggle result - ID: todo_123, Success: Yes, Current status: completed
```

### Sorun Giderme
1. **JSON dosya izinleri**: `chmod 664 todos.json`
2. **PHP versiyonu**: `php -v` ile 7.4+ kontrol edin
3. **Cache sorunları**: Hard refresh (Ctrl+F5)
4. **Network tab**: AJAX request'leri izleyin

## 🚀 Geliştirme Roadmap

### v2.0 Hedefleri
- [ ] **Multi-user support** - Kullanıcı sistemi
- [ ] **Categories & Tags** - Görev kategorileri
- [ ] **Due dates** - Bitiş tarihleri
- [ ] **Priority levels** - Öncelik seviyeleri
- [ ] **Search & Filter** - Arama ve filtreleme
- [ ] **Dark mode** - Karanlık tema
- [ ] **PWA support** - Offline çalışma
- [ ] **Database migration** - MySQL/SQLite desteği

### v2.1 Advanced Features
- [ ] **Drag & Drop** - Görev sıralaması
- [ ] **Bulk operations** - Toplu seçim/işlem
- [ ] **Recurring tasks** - Tekrarlanan görevler
- [ ] **Team collaboration** - Takım çalışması
- [ ] **API endpoints** - REST API
- [ ] **Mobile app** - React Native/Flutter

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun: `git checkout -b feature/amazing-feature`
3. Commit edin: `git commit -m 'Add amazing feature'`
4. Push edin: `git push origin feature/amazing-feature`
5. Pull Request açın

### Development Setup
```bash
# Clone repository
git clone https://github.com/yourusername/hotwire-todo-app.git

# Development server
php -S localhost:8000

# Live reload (opsiyonel)
# Browser sync veya watch script kullanabilirsiniz
```

## 📜 Lisans

MIT License - Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 🙏 Teşekkürler

- **Hotwire Team** - Turbo & Stimulus framework
- **PHP Community** - Modern PHP geliştirme
- **Contributors** - Tüm katkıda bulunanlar

---

## 📊 Demo Screenshots

### Ana Ekran

![image](https://github.com/user-attachments/assets/91521798-1ebc-40f0-a093-961c2e57c755)


### Görev Düzenleme

![image](https://github.com/user-attachments/assets/f44aad95-4aec-4fea-8565-4cc709db8384)



### Mobil Görünüm


![image](https://github.com/user-attachments/assets/9836a09d-21cd-44f0-9940-4426ab40d5c2)




---

**⭐ Eğer bu proje işinize yaradıysa, GitHub'da star vermeyi unutmayın!**

Built with ❤️ using **Hotwire** + **PHP** + **Modern Web Standards**
