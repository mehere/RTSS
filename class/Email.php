<?php

require_once 'email_lib/swift_required.php';

class Email {

    public function sendMail($from, $to) {

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
        for ($i = 0; $i < sizeof($to); $i++) {
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
            if ($attachment) {
                if (file_exists($attachment))
                    $out->attach(Swift_Attachment::fromPath($attachment));
            }
            try {
                $output = $mailer->send($out);
                $result[] = $output;
            } catch (Exception $e) {
                $result[] = $e->getMessage();
            }
        }
        return $result;
    }

}

?>
