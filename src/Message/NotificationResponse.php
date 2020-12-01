<?php
namespace Omnipay\FirstDataConnectIPN\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * FirstDataConnectIPN CompletePurchase Response
 */
class NotificationResponse extends AbstractResponse
{
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return isset($this->data['status']) && in_array($this->data['status'], ['APPROVED']);
    }

    /**
     * Get the transaction ID
     *
     * @return string
     */
    public function getTransactionId()
    {
        return isset($this->data['oid']) ? $this->data['oid'] : null;
    }

    /**
     * Get the transaction reference
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return isset($this->data['oid']) ? $this->data['oid'] : null;
    }

    /**
     * Response Message
     *
     * @return null|string
     */
    public function getMessage()
    {
        return isset($this->data['status']) ? $this->data['status'] : null;
    }
}
