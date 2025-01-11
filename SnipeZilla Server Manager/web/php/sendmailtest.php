<?php
/*
───────────────────────────────────────────────────────────────────────────
SnipeZilla Srcds Manager
───────────────────────────────────────────────────────────────────────────
Copyright (C) 2015 SnipeZilla.com

SnipeZilla Srcds Manager is free software:
you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SnipeZilla Srcds Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SnipeZilla Srcds Manager.
If not, see <http://www.gnu.org/licenses/>.

───────────────────────────────────────────────────────────────────────────
- contact:                   https://steamcommunity.com/profiles/76561197960637077
- Forum:                     https://www.snipezilla.com
- Steam Group:               https://steamcommunity.com/groups/snipezilla
- Installation Guide & Help: https://www.snipezilla.com/snipezilla-srcds-manager
───────────────────────────────────────────────────────────────────────────
*/
require_once 'session.start.php';
$_TOKEN=$_POST['token'];
require_once 'users.crc32.php';
if ( !preg_match('/a|c/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');
date_default_timezone_set("UTC");

$smtp          = $_POST['smtp'];
$smtp_port     = $_POST['smtp_port'];
$smtp_ssl      = $_POST['smtp_ssl'];
$auth_username = trim($_POST['auth_username']);
$auth_password = trim($_POST['auth_password']);
$sendmail_to   = explode(';', str_replace(array(',',';'),';',$_POST['sendmail_to']));
$sendmail_from = $_POST['sendmail_from'];
$message       = 'SnipeZilla Srcds Manager Test Message';
$subject       = 'Sz Manager Alert';
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\web.*+$/','\\',$root);

//ini_set smtp
if ( !trim($smtp) ) {
    $smtp='localhost';
}
if ( !trim($smtp_port) ) {
    $smtp_port=25;
}
if ( !trim($smtp_ssl) || $smtp_ssl != 'true' ) {
    $smtp_ssl='';
}
if ( !trim($auth_username)  ) {
    $auth_username='';
}
if ( !trim($auth_password) ) {
    $auth_password='';
}
if ( !trim($sendmail_from) ) {
    $sendmail_from='no-reply@snipezilla.com';
}

require_once($root.'bin\class.phpmailer.php');
include($root.'bin\class.smtp.php'); // optional, gets called from within class.phpmailer.php if not already loaded

$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
$mail->IsSMTP(); // telling the class to use SMTP

try {
  $mail->SMTPDebug  = 2;                           // enables SMTP debug information (for testing)
  $mail->SMTPAuth   = $auth_password != '';        // enable SMTP authentication
  $mail->SMTPSecure = ($smtp_ssl?"ssl":"");        // sets the prefix to the server
  $mail->Host       = $smtp;                       // sets SMTP server
  $mail->Port       = $smtp_port;                  // set the SMTP port for the server
  $mail->Username   = $auth_username;              // username
  $mail->Password   = $auth_password;              // password
  foreach($sendmail_to as $email) { $mail->addAddress($email); }
  $mail->SetFrom($sendmail_from);
  $mail->Subject = $subject;
  $mail->Body    = 'SnipeZilla Srcds Manager Test Message';
  $mail->isHTML(false);                            // Set email format to HTML
  $mail->Send();

} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage();   //Boring error messages from anything else!
}

