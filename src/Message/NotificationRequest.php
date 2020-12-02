<?php
namespace Omnipay\FirstDataConnectIPN\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * FirstDataConnectIPN CompletePurchase Request
 */
class NotificationRequest extends AbstractRequest implements NotificationInterface
{

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
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    /**
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    /**
     * Send the request with specified data
     *
     * @param mixed
     * @return \Omnipay\Common\Message\NotificationInterface
     */
    public function sendData($data)
    {
        return $this->response = new NotificationResponse($this, $data);
    }

    /**
     * Get hash for response
     *
     * @param string timestamp
     * @return string
     */
    public function getHash()
    {
        return $this->createHash($this->getSharedSecret() . $this->httpRequest->request->get('approval_code') . $this->httpRequest->request->get('chargetotal') . $this->getCurrencyNumeric() . $this->httpRequest->request->get('txndatetime') . $this->getStoreId());
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
    public function createHash($string)
    {
        return hash('sha256', bin2hex($string));
    }
}
