<?php
namespace Omnipay\FirstDataConnectIPN;

use Carbon\Carbon;
use Omnipay\Common\AbstractGateway;
use Omnipay\FirstDataConnectIPN\Message\AcceptNotification;

/**
 * FirstDataConnectIPN Gateway
 */
class Gateway extends AbstractGateway
{
    /**
     * Gateway name
     *
     * @return string
     */
    public function getName()
    {
        return 'FirstDataConnectIPN';
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'storeId' => '',
            'sharedSecret' => '',
            'currency' => '',
            'hashAlgorithm' => 'SHA256',
        ];
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTime()
    {
        return Carbon::now()->format('Y:m:d-H:i:s');
    }

    /**
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setCurrency($value)
    {
        return $this->setParameter('currency', $value);
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getParameter('currency');
    }

    /**
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setHashAlgorithm($value)
    {
        return $this->setParameter('hashAlgorithm', $value);
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->getParameter('hashAlgorithm');
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
     * Create hash
     *
     * @param string $string to be be hashed
     * @param string $algo to use, SHA256 or SHA512
     * @return string hashed string
     */
    public function createHash(string $txnDateTime, $chargeTotal) : string
    {
        return AcceptNotification::createHash($this->getStoreId().$txnDateTime.$chargeTotal.$this->getCurrency().$this->getSharedSecret(), $this->getHashAlgorithm());
    }

    /**
     * Receive and handle an instant payment notification (IPN)
     *
     * @return AcceptNotification
     */
    public function acceptNotification()
    {
        return new AcceptNotification($this->httpRequest, $this->getStoreId(), $this->getSharedSecret());
    }
}
