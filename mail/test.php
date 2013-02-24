<?php

require_once 'send_mail.php';

for ($i = 0; $i < 1; $i++) {
    $message = '<html><body>';
    $message .= '<h1>Hello, World!</h1>';
    $message .= '</body></html>';
    $message = '<html><body>';
    $message .= '<img src="http://css-tricks.com/examples/WebsiteChangeRequestForm/images/wcrf-header.png" alt="Website Change Request" />';
    $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
    $message .= "<tr style='background: #eee;'><td><strong>Name:</strong> </td><td>Virgil Cai</td></tr>";
    $message .= "<tr><td><strong>Email:</strong> </td><td>ryujicai@hotmail.com</td></tr>";
    $message .= "<tr><td><strong>Type of Change:</strong> </td><td>Attemp #$i</td></tr>";
    $message .= "<tr><td><strong>Urgency:</strong> </td><td>Very Urgent</td></tr>";
    $message .= "<tr><td><strong>URL To Change (main):</strong> </td><td>www.yyets.com</td></tr>";

    $to[$i] = array("name" => "Virgil Cai", "email" => "ryujicai@hotmail.com", "subject" => "FYP Email Testing", "message" => $message, "attachment" => "");    
}

for ($i = 1; $i < 2; $i++) {
    $message = '<html><body>';
    $message .= '<h1>Hello, World!</h1>';
    $message .= '</body></html>';
    $message = '<html><body>';
    $message .= '<img src="http://css-tricks.com/examples/WebsiteChangeRequestForm/images/wcrf-header.png" alt="Website Change Request" />';
    $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
    $message .= "<tr style='background: #eee;'><td><strong>Name:</strong> </td><td>Virgil Cai</td></tr>";
    $message .= "<tr><td><strong>Email:</strong> </td><td>ryujicai@hotmail.com</td></tr>";
    $message .= "<tr><td><strong>Type of Change:</strong> </td><td>Attemp #$i</td></tr>";
    $message .= "<tr><td><strong>Urgency:</strong> </td><td>Very Urgent</td></tr>";
    $message .= "<tr><td><strong>URL To Change (main):</strong> </td><td>www.yyets.com</td></tr>";

    $to[$i] = array("name" => "Virgil Cai", "email" => "ryujicai@hotmail.com", "subject" => "FYP Email Testing", "message" => $message);    
}

for ($i = 2; $i < 3; $i++) {
    $message = '<html><body>';
    $message .= '<h1>Hello, World!</h1>';
    $message .= '</body></html>';
    $message = '<html><body>';
    $message .= '<img src="http://css-tricks.com/examples/WebsiteChangeRequestForm/images/wcrf-header.png" alt="Website Change Request" />';
    $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
    $message .= "<tr style='background: #eee;'><td><strong>Name:</strong> </td><td>Virgil Cai</td></tr>";
    $message .= "<tr><td><strong>Email:</strong> </td><td>ryujicai@hotmail.com</td></tr>";
    $message .= "<tr><td><strong>Type of Change:</strong> </td><td>Attemp #$i</td></tr>";
    $message .= "<tr><td><strong>Urgency:</strong> </td><td>Very Urgent</td></tr>";
    $message .= "<tr><td><strong>URL To Change (main):</strong> </td><td>www.yyets.com</td></tr>";

    $to[$i] = array("name" => "Virgil Cai", "email" => "ryujicai@hotmail.com", "subject" => "FYP Email Testing", "message" => $message, "attachment" => "C:\Users\Virgil\Desktop\Hyflux.pdf");    
}

for ($i = 3; $i < 4; $i++) {
    $message = '<html><body>';
    $message .= '<h1>Hello, World!</h1>';
    $message .= '</body></html>';
    $message = '<html><body>';
    $message .= '<img src="http://css-tricks.com/examples/WebsiteChangeRequestForm/images/wcrf-header.png" alt="Website Change Request" />';
    $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
    $message .= "<tr style='background: #eee;'><td><strong>Name:</strong> </td><td>Virgil Cai</td></tr>";
    $message .= "<tr><td><strong>Email:</strong> </td><td>ryujicai@hotmail.com</td></tr>";
    $message .= "<tr><td><strong>Type of Change:</strong> </td><td>Attemp #$i</td></tr>";
    $message .= "<tr><td><strong>Urgency:</strong> </td><td>Very Urgent</td></tr>";
    $message .= "<tr><td><strong>URL To Change (main):</strong> </td><td>www.yyets.com</td></tr>";

    $to[$i] = array("name" => "Virgil Cai", "email" => "ryujicai@hotmail.com", "subject" => "FYP Email Testing", "message" => $message, "attachment" => "hahahahahah");    
}

$from = array("name" => "Cai Haolan", "email" => "ryujicai@gmail.com", "password" => "lanjie1314", "smtp" => "smtp.gmail.com", "port" => 465, "encryption" => "ssl");

sendMail($from, $to);
?>
