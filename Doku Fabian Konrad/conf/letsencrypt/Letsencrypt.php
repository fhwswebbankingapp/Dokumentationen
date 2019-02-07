<?php

require_once __DIR__.'//vendor/autoload.php'; //Dateien über das Composer Autoload.php abrufen 

// Config the desired paths
\LE_ACME2\Account::setCommonKeyDirectoryPath('%xampp%/apache/conf/letsencrypt/');
\LE_ACME2\Authorizer\HTTP::setDirectoryPath('%wwwroot%/acme-challenges/');

// General configs
\LE_ACME2\Connector\Connector::getInstance()->useStagingServer(true);
\LE_ACME2\Utilities\Logger::getInstance()->setDesiredLevel(\LE_ACME2\Utilities\Logger::LEVEL_INFO);



$account_email = 'fabian.konrad@student.fhws.de';

$account = !\LE_ACME2\Account::exists($account_email) ?
    \LE_ACME2\Account::create($account_email) :
    \LE_ACME2\Account::get($account_email);

// Update email address
// $account->update('new-test@example.org');

// Deactivate account
// Warning: It seems not possible to reactivate an account.
// $account->deactivate();

$subjects = [
    'fhwswebbankingapp.ddns.net' // First item will be set as common name on the certificate
];

$order = !\LE_ACME2\Order::exists($account, $subjects) ?
    \LE_ACME2\Order::create($account, $subjects) :
    \LE_ACME2\Order::get($account, $subjects);

if($order->shouldStartAuthorization(\LE_ACME2\Order::CHALLENGE_TYPE_HTTP)) {
    // Do some pre-checks, f.e. external dns checks - not required
}

if($order->authorize(\LE_ACME2\Order::CHALLENGE_TYPE_HTTP)) {
    $order->finalize();
}

if($order->isCertificateBundleAvailable()) {

    $bundle = $order->getCertificateBundle();
    $order->enableAutoRenewal();

    // Revoke certificate
    // $order->revokeCertificate($reason = 0);
}