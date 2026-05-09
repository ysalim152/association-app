<?php
// Helpers pour emails
class EmailHelper {
    public static function sendWelcomeEmail($email, $first_name) {
        $subject = 'Bienvenue sur ' . APP_NAME;
        $body = "Bienvenue $first_name,\n\n";
        $body .= "Votre compte a été créé avec succès.\n";
        $body .= "Vous pouvez vous connecter avec votre email : $email\n\n";
        $body .= "Cordialement,\n" . APP_NAME;
        return self::send($email, $subject, $body);
    }

    public static function sendPaymentReminder($email, $member_name, $due_date, $amount) {
        $subject = 'Rappel : Paiement de votre adhésion';
        $body = "Bonjour $member_name,\n\n";
        $body .= "Nous vous rappelons que votre adhésion est due le $due_date.\n";
        $body .= "Montant : $amount €\n\n";
        $body .= "Veuillez effectuer votre paiement rapidement.\n\n";
        $body .= "Cordialement,\n" . APP_NAME;
        return self::send($email, $subject, $body);
    }

    public static function sendMatchNotification($email, $member_name, $match_info) {
        $subject = 'Match prévu : ' . $match_info['opponent'];
        $body = "Bonjour $member_name,\n\n";
        $body .= "Un match est programmé pour :\n";
        $body .= "Date : " . $match_info['match_date'] . "\n";
        $body .= "Lieu : " . $match_info['location'] . "\n";
        $body .= "Équipe adverse : " . $match_info['opponent'] . "\n\n";
        $body .= "Merci de votre présence.\n\n";
        $body .= "Cordialement,\n" . APP_NAME;
        return self::send($email, $subject, $body);
    }

    public static function send($to, $subject, $body) {
        $headers = "From: " . MAIL_FROM . "\r\n";
        $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // En production, utiliser PHPMailer ou SwiftMailer
        // Pour développement, utiliser mail() natif
        if (DEBUG) {
            error_log("Email envoyé à $to: $subject");
            return true;
        }
        return mail($to, $subject, $body, $headers);
    }

    public static function queueEmail($to, $subject, $body) {
        global $pdo;
        $sql = "INSERT INTO email_queue (recipient_email, subject, body, status) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$to, $subject, $body, 'pending']);
    }

    public static function sendQueuedEmails() {
        global $pdo;
        $sql = "SELECT id, recipient_email, subject, body FROM email_queue WHERE status = ? ORDER BY created_at LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['pending']);
        $emails = $stmt->fetchAll();

        foreach ($emails as $email) {
            if (self::send($email['recipient_email'], $email['subject'], $email['body'])) {
                $update = $pdo->prepare("UPDATE email_queue SET status = ?, sent_at = NOW() WHERE id = ?");
                $update->execute(['sent', $email['id']]);
            }
        }
        return count($emails);
    }
}
