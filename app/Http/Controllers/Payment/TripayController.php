<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TripayController extends Controller
{
    public function getPaymentChannels()
    {
        $apiKey = 'DEV-A02U5RLu0ChrcK89EcbxB7ipcZbbLATnT2qVMJ3A';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT     => true,
            CURLOPT_URL               => "https://tripay.co.id/api-sandbox/merchant/payment-channel",
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HEADER            => false,
            CURLOPT_HTTPHEADER        => array(
                "Authorization: Bearer " . $apiKey
            ),
            CURLOPT_FAILONERROR       => false
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response ? json_decode($response) : $err;
    }

    public function requestTransaction($book, $method)
    {
        $apiKey       = 'DEV-A02U5RLu0ChrcK89EcbxB7ipcZbbLATnT2qVMJ3A';
        $privateKey   = '5QPaZ-uyCgB-gDkUQ-10KZg-scDUb';
        $merchantCode = 'T14095';
        $merchantRef  = 'PX-' . time();
        $amount = $book->price;

        $data = [
            'method'            => $method,
            'merchant_ref'      => $merchantRef,
            'amount'            => $book->price,
            'customer_name'     => 'Nama Pelanggan',
            'customer_email'    => 'emailpelanggan@domain.com',
            'customer_phone'    => '081234567890',
            'order_items'       => [
                [
                    'name'      => $book->title,
                    'price'     => $book->price,
                    'quantity'  => 1
                ]
            ],
            'return_url'   => 'https://domainanda.com/redirect',
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature'    => hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        // dd($response);
        return $response ? json_decode($response) : $error;
    }

    public function transactionDetail($reference)
    {
        $apiKey = 'DEV-A02U5RLu0ChrcK89EcbxB7ipcZbbLATnT2qVMJ3A';

        $payload = [
            'reference'    => $reference
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT     => true,
            CURLOPT_URL               => "https://tripay.co.id/api-sandbox/transaction/detail?" . http_build_query($payload),
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HEADER            => false,
            CURLOPT_HTTPHEADER        => array(
                "Authorization: Bearer " . $apiKey
            ),
            CURLOPT_FAILONERROR       => false,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response ? json_decode($response)->data : $err;
    }
}
