<?php
die('dasdas');
$card               = str_replace(' ','',$_GET['card']);
$explodeName        = explode(' ', $_GET['owner']);
$credit_card        = $card ? $card : '4059310181757001';
$credit_card_cvc    = $_GET['cvvForm'] ? $_GET['cvvForm'] : '123';
$email              = 'keshavjoshi99933@gmail.com';
$cclw = 'EC6DC1CC857EDD2A356334568E72DD7A8379D178796C8B63B5E6A3B6AFBA3A571BEADBEDFE002E153EAD73015D329B6BEAE525CBA2BC842A4194CE6B7947EF1F';


$hash = $credit_card.$credit_card_cvc.$email;
$data = array(
"CCLW" => $cclw,
"txType" => 'SALE',
"CMTN" => '12',
"CDSC" => 'transaction',
"CCNum" => $credit_card,
"ExpMonth" => 06,
"ExpYear" => 22,
"CVV2" => $credit_card_cvc,
"Name" => $explodeName[0] ? $explodeName[0] : 'Test',
"LastName" => $explodeName[1] ? $explodeName[1] : 'Test',
"Email" => $email,
"Address" => 'mohali',
"Tel" => '9041899933',
"Ip"=>"192.168.0.1",
"SecretHash" => hash('sha512', $hash),
);

$requestBody = http_build_query($data);
$curlURL = "https://sandbox.paguelofacil.com/rest/ccprocessing/";
$ch = curl_init($curlURL);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Accept: */*'));
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1.2');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $arresult = json_decode($response,true);
echo "<pre>"; print_r($arresult); die('dies');