<?php
declare(strict_types=1);
final class PaymentGateway {
    public function __construct(private array $config) {}
    public function isConfigured(): bool {
        return ($this->config['payment_provider'] ?? 'manual') === 'zarinpal'
            && !empty($this->config['payment']['merchant_id'])
            && !str_contains((string)$this->config['payment']['merchant_id'], 'YOUR_');
    }
    private function api(string $path, array $payload): array {
        if (!function_exists('curl_init')) throw new RuntimeException('افزونه cURL روی هاست فعال نیست.');
        $base = !empty($this->config['payment']['sandbox']) ? 'https://sandbox.zarinpal.com/pg/v4/payment/' : 'https://api.zarinpal.com/pg/v4/payment/';
        $ch=curl_init($base.$path);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE),CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25]);
        $body=curl_exec($ch);$err=curl_error($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
        if($body===false||$status<200||$status>=300) throw new RuntimeException('ارتباط با درگاه پرداخت ناموفق بود. '.$err);
        $data=json_decode($body,true);if(!is_array($data)) throw new RuntimeException('پاسخ درگاه قابل خواندن نیست.');return $data;
    }
    public function createPayment(int $orderId, int $amountRial, string $callbackUrl, string $description, string $email='', string $mobile=''): array {
        if(!$this->isConfigured()) throw new RuntimeException('شناسه پذیرنده درگاه در config.php تنظیم نشده است.');
        $res=$this->api('request.json',['merchant_id'=>$this->config['payment']['merchant_id'],'amount'=>$amountRial,'callback_url'=>$callbackUrl,'description'=>mb_substr($description,0,250),'metadata'=>['email'=>$email,'mobile'=>$mobile,'order_id'=>(string)$orderId]]);
        $d=$res['data']??[];if(empty($d['authority'])||!in_array((int)($d['code']??-1),[100,101],true)) throw new RuntimeException('درگاه درخواست پرداخت را نپذیرفت. کد: '.(string)($d['code']??'نامشخص'));
        return ['authority'=>$d['authority'],'url'=>(!empty($this->config['payment']['sandbox'])?'https://sandbox.zarinpal.com/pg/StartPay/':'https://www.zarinpal.com/pg/StartPay/').rawurlencode($d['authority']),'raw'=>$res];
    }
    public function verifyPayment(string $authority, int $amountRial): array {
        $res=$this->api('verify.json',['merchant_id'=>$this->config['payment']['merchant_id'],'amount'=>$amountRial,'authority'=>$authority]);$d=$res['data']??[];
        if(!in_array((int)($d['code']??-1),[100,101],true)) throw new RuntimeException('پرداخت توسط درگاه تأیید نشد. کد: '.(string)($d['code']??'نامشخص'));
        return ['ref_id'=>(string)($d['ref_id']??''),'raw'=>$res];
    }
}
