<?php

require_once 'lib/swift_required.php';

function sendMail($from, $to){
    $fromEmail = $from["email"];
    $fromPassword = $from["password"];
    $fromName = $from["name"];
    $fromSmtp = $from["smtp"];
    $fromPort = $from["port"];    
    $fromEncryption = $from["encryption"];
    $transport = Swift_SmtpTransport::newInstance($fromSmtp, $fromPort, $fromEncryption);
    $transport->setUsername($fromEmail);
    $transport->setPassword($fromPassword);  
    $mailer = Swift_Mailer::newInstance($transport);    
    for($i = 0; $i < sizeof($to); $i++){
        $subject = $to[$i]["subject"]; 
        $toEmail = $to[$i]["email"];
        $toName = $to[$i]["name"];
        $message = $to[$i]["message"];
        $attachment = $to[$i]["attachment"];        
        $out = Swift_Message::newInstance();
        $out->setSubject($subject);
        $out->setFrom(array($fromEmail => $fromName));
        $out->setTo(array($toEmail => $toName));
        $out->setBody($message, 'text/html');
        if ($attachment){
            if (file_exists($attachment))
            $out->attach(Swift_Attachment::fromPath($attachment));
        }        
        $result = $mailer->send($out);
    }    
}

/*
// Create the message
$message = Swift_Message::newInstance();

// Give the message a subject
$message->setSubject('FYP Mailing Testing');

// Set the From address with an associative array
$message->setFrom(array('ryujicai@gmail.com' => 'Virgil Cai'));

// Set the To addresses with an associative array
$message->setTo(array('ya0002ei@e.ntu.edu.sg' => 'Yang Wei', "caih0007@e.ntu.edu.sg" => 'Virgil Cai'));

$somemsg = '<html><body>sdf <a href="sdf">dsd</a></body></html>';

// Give it a body
$message->setBody($somemsg, 'text/html');

// Optionally add any attachments
//$message->attach(Swift_Attachment::fromPath('C:\Users\Virgil\Desktop\Hyflux.pdf'));


// Create the Transport
$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl');
$transport->setUsername('ryujicai@gmail.com');
$transport->setPassword('lanjie1314');
;

/*
  You could alternatively use a different transport such as Sendmail or Mail:

  // Sendmail
  $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

  // Mail
  $transport = Swift_MailTransport::newInstance();
 */

/*
// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

// Send the message
$result = $mailer->send($message);
*/
?>
