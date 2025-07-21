<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['subscriber_email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Invalid email address.");
    }

   $file = 'subscribers.txt';

// Read and clean existing subscribers
$subscribers = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$cleanedSubscribers = [];
foreach ($subscribers as $line) {
    $parts = explode('. ', $line, 2);
    $cleanedEmail = isset($parts[1]) ? trim($parts[1]) : trim($line);
    if (!in_array($cleanedEmail, $cleanedSubscribers)) {
        $cleanedSubscribers[] = $cleanedEmail;
    }
}

// Check if email already exists
if (in_array($email, $cleanedSubscribers)) {
    exit("You're already subscribed.");
}

// Add new email
$cleanedSubscribers[] = $email;

// Rebuild numbered list
$numberedList = "";
foreach ($cleanedSubscribers as $index => $sub) {
    $numberedList .= ($index + 1) . ". " . $sub . PHP_EOL;
}

// Save updated list
file_put_contents($file, $numberedList);

// Count updated subscribers
$subscriberCount = count($cleanedSubscribers);

    // Send confirmation to user
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jagatheswaran@fabsteele.co.in';         // Replace with your Gmail
        $mail->Password   = 'ksexlzxhrekolqst';           // ksexlzxhrekolqst
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email to Subscriber
        $mail->setFrom('fabsteel@gmail.com', 'Fabsteele.co.in Newsletter');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Thank You for Subscribing!';
        $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <img src='https://owlcode1.github.io/fabsteel/assets/159x59.png' alt='Logo' style='height: 50px;'><br><br>
            <h2 style='color:#0066cc;'>Thank You for Subscribing!</h2>
            <p>You're now part of the <strong>fabsteele.co.in</strong> community.</p>
            <p>We'll keep you updated with the latest news and tools.</p>
            <hr>
            <p><strong>Your Email:</strong> $email</p>
            <br><small>If you already subscribe, please ignore this email.</small>
        </div>";

        $mail->send();

        // Send alert to owner if subscriber count hits 100
        if ($subscriberCount == 100) {
            $adminMail = new PHPMailer(true);
            $adminMail->isSMTP();
            $adminMail->Host       = 'smtp.gmail.com';
            $adminMail->SMTPAuth   = true;
            $adminMail->Username   = 'praveenjo2001@gmail.com';
            $adminMail->Password   = 'mcjopbecsayemyxo';
            $adminMail->SMTPSecure = 'tls';
            $adminMail->Port       = 587;

            $adminMail->setFrom('fabsteel@gmail.com', 'fabsteele.co.in System');
            $adminMail->addAddress('praveenjo2001@gmail.com'); // Alert to yourself
            $adminMail->isHTML(true);
            $adminMail->Subject = '🎯 100 Subscribers Reached!';
            $adminMail->Body    = "
            <p><strong>Congratulations!</strong> You've reached 100 newsletter subscribers.</p>
            <p>Check the file: <code>subscribers.txt</code></p>";
            $adminMail->send();
        }

        echo "Thank you for subscribing!";

    } catch (Exception $e) {
        echo "Error: " . $mail->ErrorInfo;
    }

} else {
    echo "Invalid request.";
}
?>
