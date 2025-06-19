<?php
namespace Helpers;

/**
 * Stripe Helper Class
 * Maneja la integración con Stripe usando cURL si la librería oficial no está disponible
 */
class StripeHelper {
    private $secretKey;
    private $apiVersion = '2023-10-16';
    private $apiBase = 'https://api.stripe.com/v1/';
    
    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
    }
    
    /**
     * Crear un cargo (charge) en Stripe
     */
    public function createCharge($params) {
        $url = $this->apiBase . 'charges';
        
        $data = [
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'source' => $params['source'],
            'description' => $params['description'] ?? '',
            'receipt_email' => $params['receipt_email'] ?? null,
            'metadata' => $params['metadata'] ?? []
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Crear un Payment Intent
     */
    public function createPaymentIntent($params) {
        $url = $this->apiBase . 'payment_intents';
        
        $data = [
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'payment_method' => $params['payment_method'] ?? null,
            'confirmation_method' => $params['confirmation_method'] ?? 'automatic',
            'confirm' => $params['confirm'] ?? false,
            'description' => $params['description'] ?? '',
            'receipt_email' => $params['receipt_email'] ?? null,
            'metadata' => $params['metadata'] ?? []
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Confirmar un Payment Intent
     */
    public function confirmPaymentIntent($paymentIntentId, $params = []) {
        $url = $this->apiBase . 'payment_intents/' . $paymentIntentId . '/confirm';
        return $this->makeRequest('POST', $url, $params);
    }
    
    /**
     * Obtener información de un Payment Intent
     */
    public function retrievePaymentIntent($paymentIntentId) {
        $url = $this->apiBase . 'payment_intents/' . $paymentIntentId;
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Realizar solicitud HTTP a Stripe
     */
    private function makeRequest($method, $url, $data = []) {
        $ch = curl_init();
        
        // Configuración básica de cURL
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'ElProfesorHernan/1.0 (PHP)',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
                'Stripe-Version: ' . $this->apiVersion
            ]
        ]);
        
        // Configurar método HTTP
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } elseif ($method === 'GET' && !empty($data)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = 'Stripe API Error';
            if (isset($decodedResponse['error']['message'])) {
                $errorMessage = $decodedResponse['error']['message'];
            }
            throw new \Exception($errorMessage, $httpCode);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Validar webhook signature
     */
    public function validateWebhookSignature($payload, $signature, $endpointSecret) {
        $elements = explode(',', $signature);
        $signatureHash = '';
        $timestamp = '';
        
        foreach ($elements as $element) {
            list($key, $value) = explode('=', $element, 2);
            if ($key === 'v1') {
                $signatureHash = $value;
            } elseif ($key === 't') {
                $timestamp = $value;
            }
        }
        
        if (empty($signatureHash) || empty($timestamp)) {
            return false;
        }
        
        $payloadForSignature = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $payloadForSignature, $endpointSecret);
        
        return hash_equals($expectedSignature, $signatureHash);
    }
}
?>
