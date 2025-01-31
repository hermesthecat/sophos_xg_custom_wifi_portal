<?php
/*
 * Author: A. Kerem Gök
 * Sophos XG Firewall API Kullanıcı Kayıt Sistemi
 */

// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sophos XG API Yapılandırması
$config = [
    'firewall_ip' => '192.168.1.1', // Sophos XG Firewall IP adresi
    'username' => 'admin',          // API kullanıcı adı
    'password' => 'password',       // API şifresi
    'port' => '4444',              // Varsayılan Sophos API portu
    'access_time' => 4            // Saat cinsinden erişim süresi
];

// SOAP istemcisi oluştur
function createSoapClient($config)
{
    $wsdl = "https://{$config['firewall_ip']}:{$config['port']}/webconsole/APIController?wsdl";
    $opts = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ],
        'http' => [
            'header' => [
                'Authorization: Basic ' . base64_encode($config['username'] . ':' . $config['password'])
            ]
        ]
    ];
    $context = stream_context_create($opts);

    try {
        return new SoapClient($wsdl, [
            'stream_context' => $context,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true
        ]);
    } catch (SoapFault $e) {
        die("SOAP Bağlantı Hatası: " . $e->getMessage());
    }
}

// Geçici kullanıcı oluştur
function createTemporaryUser($client, $userData)
{
    $username = 'misafir_' . strtolower(str_replace(' ', '', $userData['name']));
    $password = bin2hex(random_bytes(4)); // 8 karakterlik rastgele şifre

    try {
        // Kullanıcı oluşturma SOAP isteği
        $result = $client->create([
            'type' => 'GuestUser',
            'data' => [
                'username' => $username,
                'password' => $password,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'mobile' => $userData['phone'],
                'groupname' => 'GuestUsers',
                'access_time' => $GLOBALS['config']['access_time'] * 3600 // Saniye cinsinden süre
            ]
        ]);

        return [
            'success' => true,
            'username' => $username,
            'password' => $password
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING)
    ];

    // Tüm alanların dolu olduğunu kontrol et
    if (!empty($userData['name']) && !empty($userData['email']) && !empty($userData['phone'])) {
        $client = createSoapClient($config);
        $result = createTemporaryUser($client, $userData);

        if ($result['success']) {
            // Başarılı kayıt sayfasını göster
?>
            <!DOCTYPE html>
            <html lang="tr">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Kayıt Başarılı</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 20px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        background-color: #f0f2f5;
                    }

                    .container {
                        background-color: white;
                        padding: 30px;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        width: 100%;
                        max-width: 400px;
                        text-align: center;
                    }

                    .success-message {
                        color: #28a745;
                        margin-bottom: 20px;
                    }

                    .credentials {
                        background-color: #f8f9fa;
                        padding: 15px;
                        border-radius: 4px;
                        margin-bottom: 20px;
                    }

                    .credentials p {
                        margin: 5px 0;
                    }

                    .note {
                        color: #666;
                        font-size: 14px;
                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <h1 class="success-message">Kayıt Başarılı!</h1>
                    <div class="credentials">
                        <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($result['username']); ?></p>
                        <p><strong>Şifre:</strong> <?php echo htmlspecialchars($result['password']); ?></p>
                    </div>
                    <p class="note">Bu bilgileri kullanarak wifi ağına bağlanabilirsiniz. Erişim süreniz <?php echo $config['access_time']; ?> saattir.</p>
                </div>
            </body>

            </html>
<?php
            exit;
        } else {
            $error = "Kullanıcı oluşturma hatası: " . $result['error'];
        }
    } else {
        $error = "Lütfen tüm alanları doldurun.";
    }
}

// Hata varsa ana sayfaya yönlendir
if (isset($error)) {
    header("Location: index.html");
    exit;
}
?>