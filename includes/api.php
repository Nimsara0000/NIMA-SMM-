<?php
require_once __DIR__ . '/config.php';

class ProviderApi {
    public function order($data) {
        $post = array_merge(['key' => PROVIDER_API_KEY, 'action' => 'add'], $data);
        return json_decode($this->connect($post));
    }
    public function status($orderId) {
        return json_decode($this->connect(['key' => PROVIDER_API_KEY, 'action' => 'status', 'order' => $orderId]));
    }
    public function multiStatus($ids) {
        return json_decode($this->connect(['key' => PROVIDER_API_KEY, 'action' => 'status', 'orders' => implode(',', (array)$ids)]));
    }
    public function services() {
        return json_decode($this->connect(['key' => PROVIDER_API_KEY, 'action' => 'services']));
    }
    public function refill($orderId) {
        return json_decode($this->connect(['key' => PROVIDER_API_KEY, 'action' => 'refill', 'order' => $orderId]));
    }
    public function balance() {
        return json_decode($this->connect(['key' => PROVIDER_API_KEY, 'action' => 'balance']));
    }

    private function connect($post) {
        $_post = [];
        foreach ($post as $k => $v) $_post[] = $k . '=' . urlencode($v);
        $ch = curl_init(PROVIDER_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1, CURLOPT_POST => 1, CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => join('&', $_post),
            CURLOPT_USERAGENT => 'NimaSMM/1.0',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
