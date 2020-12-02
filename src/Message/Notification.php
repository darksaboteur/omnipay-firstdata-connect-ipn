<?php
namespace Omnipay\FirstDataConnectIPN\Message;

use Symfony\Component\HttpFoundation\Request;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * FirstDataConnectIPN CompletePurchase Request
 */
class Notification implements NotificationInterface
{
    private $httpRequest;
    private $storeId;
    private $sharedSecret;

    protected $data;

    public function __construct(Request $request, $storeId, $sharedSecret)
    {
        $this->httpRequest = $request;
        $this->storeId = $storeId;
        $this->sharedSecret = $sharedSecret;

        $this->data = $this->getData();
    }

    /**
     * Get the raw data array for the message
     *
     * @return mixed
     */
    public function getData()
    {
        if ($this->getHash() != $this->getBankHash()) {
            throw new InvalidResponseException('The payment gateway response could not be verified');
        }

        return $this->httpRequest->request->all();
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

    public function getStatus()
    {
        return isset($this->data['status']) ? $this->data['status'] : null;
    }

    /**
     * Get the transaction ID
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return isset($this->data['oid']) ? $this->data['oid'] : null;
    }

    /**
     * Get the stored credit card details token
     *
     * @return string|null
     */
    public function getStoredDetailsToken() : ?string
    {
        return isset($this->data['hosteddataid']) ? $this->data['hosteddataid'] : null;
    }

    /**
     * Translate the ONEPAY status values to OmniPay status values.
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
     * Get hash for response
     *
     * @param string timestamp
     * @return string
     */
    public function getHash()
    {
        return self::createHash($this->sharedSecret . $this->httpRequest->request->get('approval_code') . $this->httpRequest->request->get('chargetotal') . $this->httpRequest->request->get('currency') . $this->httpRequest->request->get('txndatetime') . $this->storeId);
    }

    /**
     * Get hash from bank response
     *
     * @return string
     */
    protected function getBankHash()
    {
        return $this->httpRequest->request->get('response_hash');
    }

    /**
     * Create hash
     *
     * @param string
     * @return string
     */
    public static function createHash($string, $algo = 'SHA256')
    {
        return hash($algo, bin2hex($string));
    }
}
