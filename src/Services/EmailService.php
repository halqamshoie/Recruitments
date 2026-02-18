<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

class EmailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        try {
            //Server settings
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.office365.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'info@cccrc.gov.om';
            $this->mail->Password = 'FYlub084';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            //Default From
            $this->mail->setFrom('info@cccrc.gov.om', 'SQCCCRC Recruitment');
        } catch (Exception $e) {
            // Log error or handle it
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function sendWelcomeEmail($toEmail, $name)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Welcome to SQCCCRC Recruitment';
            $this->mail->Body = "
                <h1>Welcome, $name!</h1>
                <p>Thank you for registering with the Sultan Qaboos Comprehensive Cancer Care & Research Centre recruitment portal.</p>
                <p>You can now browse available vacancies and apply directly through our system.</p>
                <br>
                <p>Best regards,<br>HR Team</p>
            ";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Welcome Email Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendApplicationReceivedEmail($toEmail, $name, $jobTitle)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Application Received: ' . $jobTitle;
            $this->mail->Body = "
                <h1>Application Received</h1>
                <p>Dear $name,</p>
                <p>We have successfully received your application for the position of <strong>$jobTitle</strong>.</p>
                <p>Our HR team will review your application and get back to you shortly.</p>
                <br>
                <p>Best regards,<br>HR Team</p>
            ";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Application Email Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendStatusUpdateEmail($toEmail, $name, $jobTitle, $status)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Application Status Update: ' . $jobTitle;

            $statusMsg = "has been updated to: <strong>" . ucfirst($status) . "</strong>";
            if ($status === 'shortlisted') {
                $statusMsg = "Congratulations! You have been shortlisted for the position.";
            } elseif ($status === 'rejected') {
                $statusMsg = "Thank you for your interest. Unfortunately, we have decided to proceed with other candidates at this time.";
            }

            $this->mail->Body = "
                <h1>Application Status Update</h1>
                <p>Dear $name,</p>
                <p>The status of your application for <strong>$jobTitle</strong> $statusMsg</p>
                <p>You can view more details in your dashboard.</p>
                <br>
                <p>Best regards,<br>HR Team</p>
            ";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Status Update Email Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendPasswordResetEmail($toEmail, $token)
    {
        $resetLink = APP_URL . "/?page=reset_password&token=" . $token;
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request - SQCCCRC Recruitment';
            $this->mail->Body = "Dear User,<br><br>We received a request to reset your password. Click the link below to reset it:<br><br><a href='$resetLink'>$resetLink</a><br><br>If you did not request this, please ignore this email.<br><br>Best Regards,<br>SQCCCRC Recruitment Team";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Password Reset Email Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
    public function sendVerificationEmail($toEmail, $name, $code)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Email - SQCCCRC Recruitment';
            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 2rem;'>
                    <h1 style='color: #00AAE6; text-align: center;'>Email Verification</h1>
                    <p>Dear $name,</p>
                    <p>Thank you for registering with SQCCCRC Recruitment. Please use the verification code below to complete your registration:</p>
                    <div style='text-align: center; margin: 2rem 0;'>
                        <span style='font-size: 2rem; font-weight: bold; letter-spacing: 0.5rem; background: #f0f9ff; padding: 1rem 2rem; border-radius: 0.5rem; border: 2px dashed #00AAE6; display: inline-block;'>$code</span>
                    </div>
                    <p style='color: #64748b; font-size: 0.9rem;'>This code will expire in 15 minutes.</p>
                    <p>If you did not create an account, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br>HR Team</p>
                </div>
            ";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Verification Email Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
