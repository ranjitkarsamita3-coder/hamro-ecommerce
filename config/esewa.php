<?php
// eSewa ePay V2 UAT configuration for Hamro Ecommerce.
// These values are for testing only. Do not use UAT credentials in production.

const ESEWA_PRODUCT_CODE = 'EPAYTEST';
const ESEWA_SECRET_KEY = '8gBm/:&EnhH.1/q';
const ESEWA_PAYMENT_URL = 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';
const ESEWA_STATUS_URL = 'https://rc.esewa.com.np/api/epay/transaction/status/';

function esewa_signature(string $message): string {
    return base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
}

function esewa_request_signature(float $total_amount, string $transaction_uuid): string {
    $message = 'total_amount=' . number_format($total_amount, 2, '.', '')
        . ',transaction_uuid=' . $transaction_uuid
        . ',product_code=' . ESEWA_PRODUCT_CODE;
    return esewa_signature($message);
}

function esewa_response_signature(array $data): ?string {
    if (empty($data['signed_field_names']) || empty($data['signature'])) {
        return null;
    }

    $fields = explode(',', $data['signed_field_names']);
    $parts = [];
    foreach ($fields as $field) {
        if (!array_key_exists($field, $data)) {
            return null;
        }
        $parts[] = $field . '=' . $data[$field];
    }

    return esewa_signature(implode(',', $parts));
}

function esewa_verify_response(array $data): bool {
    $generated = esewa_response_signature($data);
    return $generated !== null
        && !empty($data['signature'])
        && hash_equals($generated, $data['signature']);
}

function esewa_check_status(float $total_amount, string $transaction_uuid): ?array {
    $url = ESEWA_STATUS_URL . '?' . http_build_query([
        'product_code' => ESEWA_PRODUCT_CODE,
        'total_amount' => number_format($total_amount, 2, '.', ''),
        'transaction_uuid' => $transaction_uuid,
    ]);

    $response = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPGET => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $response = @file_get_contents($url, false, $context);
    }

    if ($response === false || trim($response) === '') {
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}
