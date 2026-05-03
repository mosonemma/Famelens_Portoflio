<?php
/**
 * Contact Form Handler
 * Processes form submissions and sends emails
 */

// Configuration
$toEmail = 'famelensvisuals@email.com';
$subjectPrefix = 'Famelens Portfolio';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name is too long';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) > 5000) {
        $errors[] = 'Message is too long';
    }
    
    // Check for spam (honeypot field)
    $honeypot = $_POST['website'] ?? '';
    if (!empty($honeypot)) {
        // Bot detected - silently succeed
        $response['success'] = true;
        $response['message'] = 'Thank you! Your message has been sent.';
        sendJsonResponse($response);
    }
    
    if (!empty($errors)) {
        $response['message'] = implode('. ', $errors);
        sendJsonResponse($response);
    }
    
    if (empty($errors)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // Replace with your email
            $mail->Password = 'your_password'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "$subjectPrefix: New Message from $name";
            $mail->Body = nl2br(htmlspecialchars($message));

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Thank you! Your message has been sent.';
        } catch (Exception $e) {
            $response['message'] = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    sendJsonResponse($response);
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

sendJsonResponse($response);

require 'vendor/autoload.php'; // Include PHPMailer autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;