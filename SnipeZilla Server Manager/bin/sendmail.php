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
date_default_timezone_set("UTC");
$email     = unserialize(base64_decode($argv[1]));
$addresses = explode(';', str_replace(array(',',';'),';',$email['sendmail_to']));
$message   = base64_decode($argv[2]);
$subject   = 'SnipeZilla Srcds Manager';
require('class.phpmailer.php');
require('class.smtp.php');
$mail = new PHPMailer(true); // throw exceptions on errors
$mail->IsSMTP()            ; // telling the class to use SMTP
$error = '';
//Send Mail
ob_start();
try {

  $mail->SMTPDebug  = 1;                              // enables SMTP debug information
  $mail->SMTPAuth   = $email['auth_password'] != '';  // SMTP authentication
  $mail->SMTPSecure = ($email['smtp_ssl']=='true'?"ssl":"");  // SSL
  $mail->Host       = $email['smtp'];                 // SMTP server
  $mail->Port       = $email['smtp_port'];            // SMTP port
  $mail->Username   = trim($email['auth_username']);        // username
  $mail->Password   = trim($email['auth_password']);        // password
  foreach($addresses as $addresse) { $mail->addAddress($addresse); }
  $mail->SetFrom($email['sendmail_from'], $subject);
  $mail->Subject = $subject;
  $mail->Body    = $message;
  $mail->isHTML(false);
  $mail->Send();

} catch (phpmailerException $e) {
    $e->errorMessage(); //from PHPMailer
} catch (Exception $e) {
     $e->getMessage();   //from anything else
}
$error = ob_get_contents();
ob_end_clean();

//Log error
if ( $error ) {
    $error = preg_replace ('/<[^>]*>/', "\r\n", $error);
    $file = fopen('smtp-crash.txt', "w");
    fwrite($file, $error);
    fclose($file);
}