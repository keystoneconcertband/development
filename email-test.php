<?php

require("3rd-party/sendgrid-8.0.1/sendgrid-php.php");

$email = new \SendGrid\Mail\Mail(); 
$email->setFrom("webmaster@keystoneconcertband.com", "KCB Website");
$email->setSubject("Sending with SendGrid is Fun");
$email->addTo("jonathan.gillette@gmail.com", "Jonathan Gillette");
$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
$email->addContent(
    "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
);
$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}

?>