<?php

class Email {

    public static function sendMail($from, $to) {
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
        $result = array();
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
                $result[$to[$i]['accname']] = $output;
            } catch (Exception $e) {
                $result[$to[$i]['accname']] = $e->getMessage();
            }
        }
        return $result;
    }

    public static function formatEmail($name, $email_input, $sender_name, $is_cancel=false)
    {
        $table='';
        foreach ($email_input as $date => $content)
        {
            $width=array('20%', '26%', '28%', '26%');

            $headerKeyList=NameMap::$TIMETABLE['individual']['display'];
            $tableHeaderList=array_values($headerKeyList);

            $tableHead='';
            for ($i=0; $i < count($tableHeaderList); $i++)
            {
                $tableHead .= <<< EOD
                    <th width="{$width[$i]}">{$tableHeaderList[$i]}</th>
EOD;
            }

            PageConstant::escapeHTMLEntity($content);

            $tableBody='';
            $timeArr=SchoolTime::getTimeArrSub(0, -1);
            for ($i=0; $i < count($timeArr) - 1; $i++)
            {
                $teaching=$content[$i];

                if ($teaching)
                {
                    PageConstant::escapeHTMLEntity($teaching);
                    $teaching['class']=implode(", ", $teaching['class']);
                    if ($teaching['skipped'])
                    {
                        $teaching['skipped']['class']=implode(", ", $teaching['skipped']['class']);
                    }

                    $style='';
                    switch ($teaching['attr'])
                    {
                        case -1:
                            $style='style="text-decoration: line-through"';
                            break;
                        case 1:
                            $style='style="text-decoration: underline"';
                            break;
                        case 2:
                            $style='style="font-weight: bold; color: red"';
                            break;
                    }

                    $timetableEntry=array();
                    foreach (array_slice($headerKeyList, 1) as $key => $value)
                    {
                        $skippedPart=$teaching['skipped'][$key];
                        if ($skippedPart)
                        {
                            $skippedPart= <<< EOD
    <div style="color: black;">(<span style="text-decoration: line-through;">$skippedPart</span>)</div>
EOD;
                        }
                        $timetableEntry[]= <<< EOD
    <span $style>&nbsp;{$teaching[$key]}&nbsp;{$skippedPart}</span>
EOD;
                    }

                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry));
                    $tableBody .= <<< EOD
    <tr><td>{$timeArr[$i]} - {$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
                else
                {
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['individual']['display'])-1, '&nbsp;')));
                    $tableBody .= <<< EOD
    <tr><td>{$timeArr[$i]} - {$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
            }

            $table .= <<< EOD
    <p><strong>$date</strong></p>
    <table cellspacing="0" cellpadding="10" border="1" width="100%" style="text-align: center; font-size: 14px; font-family: Arial">
        <thead>
            <tr>
                $tableHead
            </tr>
        </thead>
        <tbody>
            $tableBody
        </tbody>
    </table>
EOD;
            
        }
            
        $para=$is_cancel?"Ypur lessons have been cancelled.":"You have been allocated relief lessons.";

        return <<< EOD
<html>
<body>
	<p>Dear $name:</p>
	<p>$para Your updated timetable is as below:</p>
    <p style="color: red">(Relief classes are in bold. For AED: classes underlined are not mandatory).</p>
    $table
	<p>If you have any query, please contact admin. Thanks.
    </p>
    <p>Best Regards</p>
    <p>$sender_name</p>
</body>
</html>
EOD;
    }

}

?>
