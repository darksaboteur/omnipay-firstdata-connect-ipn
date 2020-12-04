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
     * Get the stored credit card details token
     *
     * @return string|null
     */
    public function getStoredDetailsToken() : ?string
    {
        return $this->data['hosteddataid'] ?? null;
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
