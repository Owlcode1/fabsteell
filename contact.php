<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot check
    if (!empty($_POST['website'])) {
        exit("Spam detected.");
    }

    // Sanitize inputs
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $number  = htmlspecialchars($_POST['number']);
    $subject = htmlspecialchars($_POST['subject']);
    $company = htmlspecialchars($_POST['company']);
    $message = htmlspecialchars($_POST['message']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Invalid email.");
    }

    // SMTP Configuration
    $smtpHost = 'smtp.gmail.com';
    $smtpUser = 'praveenjo2001@gmail.com'; // Replace with your Gmail
    $smtpPass = 'mcjopbecsayemyxo';        // Gmail App Password
    $smtpPort = 587;

    $ownerMail = new PHPMailer(true);
    $userMail  = new PHPMailer(true);

    try {
        // Common SMTP settings
        foreach ([$ownerMail, $userMail] as $mail) {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $smtpPort;
            $mail->isHTML(true);
        }

        // 📩 Email to You (Site Owner)
        $ownerMail->setFrom($smtpUser, 'FabSteel Contact Form');
        $ownerMail->addAddress($smtpUser);
        $ownerMail->Subject = "New Contact: $subject";
        $ownerMail->Body = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Number:</strong> $number</p>
            <p><strong>Company:</strong> $company</p>
            <p><strong>Message:</strong><br>$message</p>
        ";

        // 📩 Confirmation to User
        $userMail->setFrom($smtpUser, 'FabSteel');
        $userMail->addAddress($email);
        $userMail->Subject = "Confirmation: We've received your message!";
        $userMail->Body = "
            <div style='font-family:sans-serif;border:1px solid #ddd;padding:20px;'>
                <img src='https://owlcode1.github.io/fabsteel/assets/159x59.png' alt='FabSteel Logo' width='150'>
                <h3>Hello $name,</h3>
                <p>Thank you for contacting FabSteel. Here's a copy of your message:</p>
                <hr>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong><br>$message</p>
                <hr>
                <p>We will respond as soon as possible.</p>
                <p style='color:gray;'>– FabSteel Team</p>
            </div>
        ";

        $ownerMail->send();
        $userMail->send();

        echo "Message and confirmation sent successfully.";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$ownerMail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
