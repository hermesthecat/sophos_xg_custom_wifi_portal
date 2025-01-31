# Sophos XG Wifi Misafir Portalı

HOHOHO

Bu uygulama, Sophos XG Firewall üzerinde geçici wifi kullanıcıları oluşturmak için geliştirilmiş bir web portalıdır.

## Özellikler

- Misafir kullanıcılar için kayıt formu
- Otomatik geçici kullanıcı oluşturma
- Belirli süre için internet erişimi
- Güvenli SOAP API entegrasyonu

## Kurulum

1. Dosyaları web sunucunuza yükleyin
2. `register.php` dosyasındaki yapılandırma ayarlarını düzenleyin:
   ```php
   $config = [
       'firewall_ip' => 'SOPHOS_XG_IP_ADRESI',
       'username' => 'API_KULLANICI_ADI',
       'password' => 'API_SIFRESI',
       'port' => '4444',
       'access_time' => 24
   ];
   ```

## Sophos XG Firewall Ayarları

1. Yönetici panelinde API erişimini etkinleştirin:

   - Web Admin > System Services > API Configuration
   - "Enable API Configuration" seçeneğini işaretleyin
   - API portu varsayılan olarak 4444'tür

2. Misafir kullanıcılar için grup oluşturun:
   - Authentication > Groups
   - "Add Group" seçeneğini tıklayın
   - Grup adı olarak "GuestUsers" girin
   - Gerekli internet erişim politikalarını tanımlayın

## Güvenlik Notları

- API kimlik bilgilerini güvenli bir şekilde saklayın
- HTTPS kullanarak güvenli bağlantı sağlayın
- Firewall üzerinde uygun erişim politikaları tanımlayın

## Yazar

A. Kerem Gök

HOHOHO
