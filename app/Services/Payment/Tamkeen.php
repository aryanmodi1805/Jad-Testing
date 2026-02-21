<?php

namespace App\Services\Payment;

use App\Services\Payment\Concerns\KeysData;
use App\Services\Payment\Concerns\Payment;
use App\Services\Payment\Concerns\PaymentGetWay;
use App\Services\Payment\Tamkeen\Actions\ManageAccount;
use App\Services\Payment\Tamkeen\Actions\ManagesPayments;
use App\Services\Payment\Tamkeen\MakesHttpRequests;
use GuzzleHttp\Client as HttpClient;

class Tamkeen extends Payment
{
    use MakesHttpRequests, ManageAccount, ManagesPayments;
    /**
     * The Tamkeen username.
     *
     * @var string
     */
    protected $username;

    /**
     * The Tamkeen password.
     *
     * @var string
     */
    protected $password;

    /**
     * The Tamkeen encryption key.
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     * The Tamkeen service provider id (spId).
     *
     * @var string
     */
    protected $serviceProviderId;

    /**
     * The Tamkeen certificate path.
     *
     * @var string
     */
    protected $certificatePath;

    /**
     * The Tamkeen certificate password.
     *
     * @var string
     */
    protected $certificatePassword;

    /**
     * The Guzzle HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * Number of seconds a request is retried.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new Tamkeen instance.
     *
     * @param  string|null  $apiKey
     * @param  \GuzzleHttp\Client|null  $guzzle
     * @return void
     */
    public function __construct(array $data)
    {
        if (!is_null($data[KeysData::UserName])) {
            $this->setUsername($data[KeysData::UserName]);
        }

        if (!is_null($data[KeysData::Password])) {
            $this->setPassword($data[KeysData::Password]);
        }

        if (!is_null($data[KeysData::PublicKey])) {
            $this->setServiceProviderId($data[KeysData::PublicKey]);
        }

        if (!is_null($data[KeysData::Key])) {
            $this->setEncryptionKey($data[KeysData::Key]);
        }

        if (!is_null($data[KeysData::CertificatePath]) && !is_null($data[KeysData::CertificatePassword])) {
            $this->setCertificate($data[KeysData::CertificatePath], $data[KeysData::CertificatePassword]);
        }



        $this->guzzle = new HttpClient([
            'base_uri' => "https://www.tamkeen.com.ye:{$data[KeysData::Port]}/CashPay/api/",
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'encPassword' => $this->encryptPassword($this->encryptionKey, $this->password),
            ],
            'verify' => false,
        ]);


    }

    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array  $collection
     * @param  string  $class
     * @param  array  $extraData
     * @return array
     */
    protected function transformCollection($collection, $class, $extraData = [])
    {
        return array_map(function ($data) use ($class, $extraData) {
            return new $class($data + $extraData, $this);
        }, $collection);
    }

    /**
     * Set the merchant username.
     *
     * @param  string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the merchant merchantpassword.
     *
     * @param  string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the service provider id.
     *
     * @param  string $serviceProviderId
     * @return $this
     */
    public function setServiceProviderId($serviceProviderId)
    {
        $this->serviceProviderId = $serviceProviderId;

        return $this;
    }

    /**
     * Set the encryption key.
     *
     * @param  string $encryptionKey
     * @return $this
     */
    public function setEncryptionKey(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;

        return $this;
    }

    /**
     * Set the certificate path.
     *
     * @param  string $certificatePath
     * @param  mixed $certificatePassword
     * @return $this
     */
    public function setCertificate(string $certificatePath, $certificatePassword)
    {
        $this->certificatePath = $certificatePath;
        $this->certificatePassword = $certificatePassword;

        return $this;
    }

    /**
     * Set a new timeout.
     *
     * @param  int  $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }



    private function encryptPassword($key, $plaintext)
    {
        $method = 'aes-256-cbc';
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

        return base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));
    }

    public static function getKeysData(): array
    {
        return [
            KeysData::UserName => '',
            KeysData::Password => '',
            KeysData::PublicKey => '',
            KeysData::Key => '',
            KeysData::CertificatePath => '',
            KeysData::CertificatePassword => '',
            KeysData::Port => '61890',
        ];
    }

    public static function getName(): string
    {
        return PaymentService::paymentsList[static::getProviderName()];
    }

    public static function needOtp(): bool
    {
        return true;
    }

    public static function needReceiptNumber(): bool
    {
        return false;
    }

    public static function getProviderName(): string
    {
        return 'cash_pay';
    }
}
