<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\Database;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    /**
     * Send an email via Google SMTP
     *
     * @param string|array $to Receiver email address (string) or array of addresses
     * @param string $subject Subject
     * @param string $content Content (HTML)
     * @param array $attachments Array of file paths to attach (optional)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send($to, $subject, $content, $attachments = [], $shouldLog = true, $extraData = []) {
        $mail = new PHPMailer(true);
        $result = ['success' => false, 'message' => ''];

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? ''; 
            $mail->Password   = $_ENV['SMTP_PASS'] ?? ''; 
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 465;
            $mail->Timeout    = 10; // Timeout after 10 seconds

            // Dynamic Encryption based on port
            if ($mail->Port == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            // Bypass certificate verification for intercepted connections (common in some hostings)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Charset
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($mail->Username, $_ENV['SMTP_FROM_NAME'] ?? 'Neuron AI Admin');
            
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Attachments
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (is_array($file)) {
                        // [path, name] format
                        if (file_exists($file[0])) {
                            $mail->addAttachment($file[0], $file[1]);
                        }
                    } else {
                        // String path format
                        if (file_exists($file)) {
                            $mail->addAttachment($file);
                        }
                    }
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;
            $mail->AltBody = strip_tags($content);

            $mail->send();
            $result['success'] = true;
            $result['message'] = 'Message has been sent';

            // Log Success
            if ($shouldLog) {
                self::log(is_array($to) ? implode(',', $to) : $to, $subject, $content, 'success', null, $extraData);
            }

        } catch (Exception $e) {
            $result['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            
            // Always log to system error log on failure
            error_log("MAIL FAILURE: To: " . (is_array($to) ? implode(',', $to) : $to) . " | Subject: {$subject} | Error: {$mail->ErrorInfo}");

            // Log Failure to DB
            if ($shouldLog) {
                self::log(is_array($to) ? implode(',', $to) : $to, $subject, $content, 'fail', $mail->ErrorInfo, $extraData);
            }
        }

        return $result;
    }

    /**
     * Log the mail sending result
     */
    private static function log($recipient, $subject, $content, $status, $error = null, $extraData = []) {
        try {
            $db = Database::getInstance();
            $logContent = $extraData['log_content'] ?? $content;

            $stmt = $db->prepare("INSERT INTO mail_logs (recipient, subject, content, status, error_message, sender_name, sender_phone, sender_email, target_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $recipient,
                $subject, 
                $logContent, 
                $status, 
                $error,
                $extraData['sender_name'] ?? null,
                $extraData['sender_phone'] ?? null,
                $extraData['sender_email'] ?? null,
                $extraData['target_info'] ?? null
            ]);
        } catch (\Exception $e) {
            // Logging failed, maybe write to file or ignore
            error_log("Mail logging failed: " . $e->getMessage());
        }
    }
}
