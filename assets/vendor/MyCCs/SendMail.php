<?php
// SendMail.php
// A simple script to take the information from a contact us
// form and e-mail it.

//error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr";
}
//set error handler
set_error_handler("customError");

ini_set("include_path", '/home2/stjamew3/php:' . ini_get("include_path") );  // Bluehost control panel PHP extensions

$emailto = 'timothykozak@yahoo.com';
$toname = 'Web Master';
$emailfrom = 'contact@wornoutoldman.com';
$fromname = 'Parish Website';
$subject = $_POST['subject'];
$messagebody = $_POST['name'] . ' at ' . $_POST['email'] . ' sent the following message:' .  "\r\n\r\n" . $_POST['message'];
$headers =
    'Return-Path: ' . $emailfrom . "\r\n" .
    'From: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
    'X-Priority: 3' . "\r\n" .
    'X-Mailer: PHP ' . phpversion() . "\r\n" .
    'Reply-To: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Transfer-Encoding: 8bit' . "\r\n" .
    'Content-Type: text/plain; charset=UTF-8' . "\r\n";
$params = '-f ' . $emailfrom;

if (mail($emailto, $subject, $messagebody, $headers, $params))
    echo 'OK';

?>