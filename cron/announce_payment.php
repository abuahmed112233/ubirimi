<?php

use Ubirimi\Container\UbirimiContainer;

use Ubirimi\Util;

use Ubirimi\SystemProduct;

/* check locking mechanism */
if (file_exists(__DIR__ . '/announce_payment.lock')) {
    $fp = fopen('announce_payment.lock', 'w+');
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        echo "Unable to obtain lock for announce_payment task.\n";
        exit(-1);
    }
}

require_once __DIR__ . '/../web/bootstrap_cli.php';

/*
 * this cronjob sends an email to the client notifying him that an invoice has been generated.
 */
$clients = UbirimiContainer::get()['repository']->getRepository(UbirimiClient::class)->getCurrentMonthAndDayPayingCustomers();
/**
 * send the email to every client administrator
*/

while ($clients && $client = $clients->fetch_array(MYSQLI_ASSOC)) {
    $clientId = $client['id'];
    $emailSubject = 'Ubirimi - Invoice UBR ' . $client['invoice_number'];
    $mailer = Util::getUbirmiMailer('contact');
    $clientAdministrators = $this->getRepository(UbirimiClient::class)->getAdministrators($clientId);
    $clientAdministrator = $clientAdministrators->fetch_array(MYSQLI_ASSOC);
    $emailBody = Util::getTemplate('_announce_payment.php', array(
        'clientAdministrator' => $clientAdministrator['first_name'] . ' ' . $clientAdministrator['last_name'],
        'clientDomain' => $client['company_domain'],
        'invoiceNumber' => $client['invoice_number'],
        'invoiceAmount' => $client['invoice_amount'])
    );

    $message = Swift_Message::newInstance($emailSubject)
        ->setFrom(array('contact@ubirimi.com'))
        ->setTo($client['contact_email'])
        ->setSubject($emailSubject)
        ->setBody($emailBody, 'text/html')
        ->attach(Swift_Attachment::fromPath(UbirimiContainer::get()['invoice.path'] . '/' . sprintf('Ubirimi_%d.pdf', $client['invoice_number'])));

    try {
        $mailer->send($message);
    } catch (Exception $e) {
        UbirimiContainer::get()['repository']->add($clientId, SystemProduct::SYS_PRODUCT_YONGO, $client['id'], 'Could not send announce payment email', Util::getServerCurrentDateTime());
    }
}

if (null !== $fp) {
    fclose($fp);
}