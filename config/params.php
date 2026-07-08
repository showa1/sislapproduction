<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'seckey' => sha1('ehealthsys240117'),
    'bsVersion' => '5.x',

    // ============================================================
    // PASSWORD KHUSUS MODUL KEUANGAN
    // Password default: Keuangan@PMC2026
    // Untuk mengganti password, jalankan perintah PHP berikut:
    //   php -r "echo password_hash('PasswordBaru', PASSWORD_BCRYPT, ['cost' => 10]);"
    // Lalu tempel hasilnya di bawah ini.
    // ============================================================
    'keuangan_password_hash' => '$2y$10$WeVrkLkccq1JrDDLR2rv/u4j/EGyO3HAmGx3Qs43V7nGpmwvTrQIK',
];
