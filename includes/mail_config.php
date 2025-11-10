<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // autoload PHPMailer

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // 🔧 Konfigurasi SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gorengchicken.noreply@gmail.com'; // email pengirim kamu
        $mail->Password = 'hcjpklnwfrkkprpy'; // App password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // 📨 Pengaturan pengirim
        $mail->setFrom('gorengchicken.noreply@gmail.com', 'Goreng Chicken');
        $mail->addAddress($to);

        // ✅ Tambahkan logo agar tampil di email (PERBAIKAN UTAMA)
        $logoPath = __DIR__ . '/../assets/img/Logo.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_gc'); // <-- penting: id ini akan dipanggil di HTML
        }

        // 📩 Konten email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return ['success' => true, 'message' => 'Email berhasil dikirim'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $mail->ErrorInfo];
    }
}
