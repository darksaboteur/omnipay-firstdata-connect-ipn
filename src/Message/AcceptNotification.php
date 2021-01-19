<?php
namespace Omnipay\FirstDataConnectIPN\Message;

use Symfony\Component\HttpFoundation\Request;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * FirstDataConnectIPN Accept Notification
 */
class AcceptNotification implements NotificationInterface
{
    private $httpRequest;
    private $storeId;
    private $sharedSecret;
    private $isNotification;

    protected $data;

    const AUTH_3D_SECURE_SUCCESSFUL = 1;
    const AUTH_3D_SECURE_SUCCESSFUL_NO_AVV = 2;
    const AUTH_3D_SECURE_FAILED = 3;
    const AUTH_3D_SECURE_ATTEMPT = 4;
    const AUTH_3D_SECURE_UNABLE_DS = 5;
    const AUTH_3D_SECURE_UNABLE_ACS = 6;
    const AUTH_3D_SECURE_CARDHOLDER_NOT_ENROLLED = 7;

    public function __construct(Request $request, $storeId, $sharedSecret, bool $isNotification = true)
    {
        $this->httpRequest = $request;
        $this->storeId = $storeId;
        $this->sharedSecret = $sharedSecret;
        $this->isNotification = $isNotification;

        $this->data = $this->getData();
    }

    /**
     * Get the raw data array for the message
     *
     * @return mixed
     */
    public function getData() : array
    {   
        if ($this->isNotification) {
            if ($this->createNotificationHash() != $this->getNotificationHash()) {
                throw new InvalidResponseException('The payment gateway notification could not be verified');
            }
        } else {
            if ($this->createResponseHash() != $this->getResponseHash()) {
                throw new InvalidResponseException('The payment gateway response could not be verified');
            }
        }

        return $this->httpRequest->request->all();
    }

    protected function getStatus() : ?string
    {
        return $this->data['status'] ?? null;
    }

    /**
     * Get the transaction ID
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->data['oid'] ?? null;
    }

    /**
     * Translate the First Data status values to OmniPay status values.
     */
    public function getTransactionStatus()
    {
        $status = $this->getStatus();

        if ($status == 'APPROVED') {
            return static::STATUS_COMPLETED;
        } elseif ($status == 'WAITING') {
            return static::STATUS_PENDING;
        }
        return static::STATUS_FAILED;
    }

    /**
     * Response Message
     *
     * @return null|string
     */
    public function getMessage()
    {
        return $this->data['fail_reason'] ?? $this->getStatus();
    }

    /**
     * Get the last 4 digits from the card
     *
     * @return null|string
     */
    public function getLastFour() : ?string
    {
        if (isset($this->data['cardnumber']) && strlen($this->data['cardnumber']) >= 4) {
            $lastFour = substr($this->data['cardnumber'], -4);
            if (ctype_digit($lastFour)) {
                return $lastFour;
            }
        }
        return null;
    }

    /**
     * Get the expiry month from the card
     *
     * @return null|string
     */
    public function getExpiryMonth() : ?string
    {
        if (isset($this->data['expmonth']) && $this->data['expmonth']) {
            return str_pad($this->data['expmonth'], 2, "0", STR_PAD_LEFT);
        }
        return null;
    }

    /**
     * Get the expiry year from the card
     *
     * @return null|string
     */
    public function getExpiryYear() : ?string
    {
        if (isset($this->data['expyear']) && $this->data['expyear']) {
            return str_pad($this->data['expyear'], 2, "0", STR_PAD_LEFT);
        }
        return null;
    }

    /**
     * Get the 3d secure response code
     *
     * @return int|null
     */
    public function get3DSecureStatus() : ?int
    {
        return $this->data['response_code_3dsecure'] ?? null;
    }

    /**
     * Get the stored credit card details token
     *
     * @return string|null
     */
    public function getStoredDetailsToken() : ?string
    {
        return $this->data['hosteddataid'] ?? null;
    }

    /**
     * Get the amount the transaction was for
     *
     * @return string|null
     */
    public function getAmount() : ?string
    {
        return $this->data['chargetotal'] ?? null;
    }

    /**
     * Get hash from bank response
     *
     * @return string
     */
    protected function getResponseHash() : string
    {
        return $this->httpRequest->request->get('response_hash');
    }

    /**
     * Get hash from bank notification
     *
     * @return string
     */
    protected function getNotificationHash() : string
    {
        return $this->httpRequest->request->get('notification_hash');
    }

    /**
     * Get hash for response
     *
     * @param string timestamp
     * @return string
     */
    public function createResponseHash() : string
    {
        return self::createHash($this->sharedSecret . $this->httpRequest->request->get('approval_code') . $this->httpRequest->request->get('chargetotal') . $this->httpRequest->request->get('currency') . $this->httpRequest->request->get('txndatetime') . $this->storeId, $this->httpRequest->request->get('hash_algorithm'));
    }

    /**
     * Get hash for notification
     *
     * @param string timestamp
     * @return string
     */
    public function createNotificationHash() : string
    {
        return self::createHash($this->httpRequest->request->get('chargetotal') . $this->sharedSecret . $this->httpRequest->request->get('currency') . $this->httpRequest->request->get('txndatetime') . $this->storeId . $this->httpRequest->request->get('approval_code'), $this->httpRequest->request->get('hash_algorithm'));
    }

    /**
     * Create hash
     *
     * @param string $string to be be hashed
     * @param string $algo to use, SHA256 or SHA512
     * @return string hashed string
     */
    public static function createHash($string, $algo = 'SHA256') : string
    {
        return hash($algo, bin2hex($string));
    }
}
