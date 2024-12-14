<?php
require('/home/admin/public_html/gmail/PHPMailer/PHPMailerAutoload.php');

define('GUSER', 'mig@remo.co.id'); // GMail username
define('GPWD', '123niaga'); // GMail password


function phpAlert($msg) {
    echo '<script type="text/javascript">alert("' . $msg . '")</script>';
}

function smtpmailer($to, $from, $from_name, $subject, $body) { 
	global $error;
	$mail = new PHPMailer();  // create a new object
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true;  // authentication enabled
	$mail->SMTPSecure = ''; // secure transfer enabled REQUIRED for GMail
	$mail->Host = '156.67.217.150';
	$mail->Port = 25; 
	$mail->SMTPAutoTLS = false;
	$mail->Username = GUSER;  
	$mail->Password = GPWD;           
	$mail->SetFrom($from, $from_name);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddAddress($to);
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
	} else {
		$error = 'Message sent!';
		return true;
	}
}

if (smtpmailer('rifkydion.project@gmail.com', 'mig@remo.co.id', 'mig', 'test mail message', 'Hello World!')) {
	phpAlert($error);
}
else
{
	phpAlert($error);
}

?>

