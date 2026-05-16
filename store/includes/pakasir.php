<?php
/**
 * Pakasir QRIS Payment Gateway Integration
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/includes/database.php';
require_once BASE_PATH . '/includes/helpers.php';

class Pakasir {
    private $api_key;
    private $merchant_id;
    private $webhook_secret;
    private $api_url;

    public function __construct() {
        $this->api_key = get_setting('pakasir_api_key', PAKASIR_API_KEY);
        $this->merchant_id = get_setting('pakasir_merchant_id', PAKASIR_MERCHANT_ID);
        $this->webhook_secret = get_setting('pakasir_webhook_secret', PAKASIR_WEBHOOK_SECRET);
        $this->api_url = PAKASIR_API_URL;
    }

    /**
     * Create QRIS payment
     */
    public function createPayment($order_id, $amount, $description = '') {
        $order = db()->fetch("SELECT * FROM orders WHERE id = ?", [$order_id]);
        if (!$order) return ['success' => false, 'message' => 'Order tidak ditemukan'];

        $payload = [
            'merchant_id' => $this->merchant_id,
            'amount' => (int) $amount,
            'reference' => $order['invoice'],
            'description' => $description ?: 'Pembayaran ' . $order['invoice'],
            'callback_url' => SITE_URL . '/api/webhook-pakasir.php',
            'return_url' => SITE_URL . '/pages/payment-status.php?invoice=' . $order['invoice'],
            'expiry_minutes' => (int) get_setting('payment_expiry_minutes', PAYMENT_EXPIRY_MINUTES),
        ];

        $response = $this->request('POST', '/payment/create-qris', $payload);

        if ($response && isset($response['success']) && $response['success']) {
            $payment_data = $response['data'] ?? $response;
            $expiry = date('Y-m-d H:i:s', strtotime('+' . $payload['expiry_minutes'] . ' minutes'));

            db()->insert('payments', [
                'order_id' => $order_id,
                'user_id' => $order['user_id'],
                'amount' => $amount,
                'method' => 'qris',
                'gateway' => 'pakasir',
                'gateway_ref' => $payment_data['payment_id'] ?? $payment_data['reference'] ?? $order['invoice'],
                'qris_url' => $payment_data['qris_url'] ?? $payment_data['qr_url'] ?? '',
                'qris_string' => $payment_data['qris_string'] ?? $payment_data['qr_string'] ?? '',
                'status' => 'pending',
                'expired_at' => $expiry,
            ]);

            return [
                'success' => true,
                'payment_id' => db()->lastInsertId(),
                'qris_url' => $payment_data['qris_url'] ?? $payment_data['qr_url'] ?? '',
                'qris_string' => $payment_data['qris_string'] ?? $payment_data['qr_string'] ?? '',
                'expiry' => $expiry,
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'Gagal membuat pembayaran QRIS',
        ];
    }

    /**
     * Verify webhook callback signature
     */
    public function verifyWebhook($payload, $signature) {
        if (empty($this->webhook_secret)) return true;
        $computed = hash_hmac('sha256', $payload, $this->webhook_secret);
        return hash_equals($computed, $signature);
    }

    /**
     * Process webhook callback
     */
    public function processWebhook($data) {
        $reference = $data['reference'] ?? $data['merchant_ref'] ?? '';
        $status = $data['status'] ?? '';
        $payment_id = $data['payment_id'] ?? $data['id'] ?? '';

        if (empty($reference)) {
            return ['success' => false, 'message' => 'Reference not found'];
        }

        $order = db()->fetch("SELECT * FROM orders WHERE invoice = ?", [$reference]);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        if ($order['status'] === 'completed' || $order['status'] === 'paid') {
            return ['success' => true, 'message' => 'Already processed'];
        }

        $payment = db()->fetch(
            "SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1",
            [$order['id']]
        );

        if ($status === 'PAID' || $status === 'paid' || $status === 'success' || $status === 'settlement') {
            db()->beginTransaction();
            try {
                db()->update('payments', [
                    'status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'callback_data' => json_encode($data),
                ], 'id = ?', [$payment['id']]);

                db()->update('orders', [
                    'status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$order['id']]);

                $order_items = db()->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
                $all_delivered = true;

                foreach ($order_items as $item) {
                    $delivered = deliver_product($order['id'], $item['id'], $item['product_id'], $item['quantity']);
                    if (!$delivered) {
                        $all_delivered = false;
                    }
                }

                if ($all_delivered) {
                    db()->update('orders', [
                        'status' => 'completed',
                        'completed_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$order['id']]);
                } else {
                    db()->update('orders', [
                        'status' => 'processing',
                    ], 'id = ?', [$order['id']]);
                }

                db()->commit();
                return ['success' => true, 'message' => 'Payment processed successfully'];
            } catch (\Exception $e) {
                db()->rollback();
                error_log('Webhook processing error: ' . $e->getMessage());
                return ['success' => false, 'message' => 'Processing error'];
            }
        }

        if ($status === 'EXPIRED' || $status === 'expired') {
            db()->update('payments', ['status' => 'expired', 'callback_data' => json_encode($data)], 'id = ?', [$payment['id']]);
            db()->update('orders', ['status' => 'cancelled'], 'id = ?', [$order['id']]);
            return ['success' => true, 'message' => 'Payment expired'];
        }

        if ($status === 'FAILED' || $status === 'failed') {
            db()->update('payments', ['status' => 'failed', 'callback_data' => json_encode($data)], 'id = ?', [$payment['id']]);
            db()->update('orders', ['status' => 'cancelled'], 'id = ?', [$order['id']]);
            return ['success' => true, 'message' => 'Payment failed'];
        }

        return ['success' => true, 'message' => 'Status noted: ' . $status];
    }

    /**
     * Check payment status
     */
    public function checkStatus($payment_id) {
        $payment = db()->fetch("SELECT * FROM payments WHERE id = ?", [$payment_id]);
        if (!$payment) return null;

        if (!empty($payment['gateway_ref'])) {
            $response = $this->request('GET', '/payment/status/' . $payment['gateway_ref']);
            if ($response && isset($response['data'])) {
                return $response['data'];
            }
        }

        return [
            'status' => $payment['status'],
            'amount' => $payment['amount'],
            'gateway_ref' => $payment['gateway_ref'],
        ];
    }

    /**
     * API request helper
     */
    private function request($method, $endpoint, $data = []) {
        $url = $this->api_url . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->api_key,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('Pakasir API Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $result = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300) {
            return $result;
        }

        error_log('Pakasir API HTTP ' . $http_code . ': ' . $response);
        return $result;
    }
}

function pakasir() {
    return new Pakasir();
}
