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
            'timezone' => '',
            'liveEndpoint' => 'https://www.ipg-online.com/connect/gateway/processing',
            'testEndpoint' => 'https://test.ipg-online.com/connect/gateway/processing',
        ];
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->getParameter('testEndpoint') : $this->getParameter('liveEndpoint');
    }

    /**
     * Setter
     *
     * @return string
     */
    public function setTimezone($value)
    {
        return $this->setParameter('timezone', $value);
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->getParameter('timezone');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTime()
    {
        return Carbon::now($this->getTimezone())->format('Y:m:d-H:i:s');
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
     * Create Request hash used for direct post to First Data
     *
     * @param string $string to be be hashed
     * @param string $algo to use, SHA256 or SHA512
     * @return string hashed string
     */
    public function createRequestHash(string $txnDateTime, $chargeTotal) : string
    {
        return AcceptNotification::createHash($this->getStoreId().$txnDateTime.$chargeTotal.$this->getCurrency().$this->getSharedSecret(), $this->getHashAlgorithm());
    }

    /**
     * Receive and handle an instant payment notification (IPN)
     *
     * @return AcceptNotification
     */
    public function acceptNotification(bool $isNotification = true)
    {
        return new AcceptNotification($this->httpRequest, $this->getStoreId(), $this->getSharedSecret(), $isNotification);
    }

    /**
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setLiveEndpoint($value)
    {
        return $this->setParameter('liveEndpoint', $value);
    }
    
    /**
     * Setter
     *
     * @param string
     * @return $this
     */
    public function setTestEndpoint($value)
    {
        return $this->setParameter('testEndpoint', $value);
    }

}
