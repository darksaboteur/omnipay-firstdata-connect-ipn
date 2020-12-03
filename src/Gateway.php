<?php
namespace Omnipay\FirstDataConnectIPN;

use Message\AcceptNotification;
use Omnipay\Common\AbstractGateway;

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
        ];
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
    public function createHash(string $string, $algo = 'SHA256') : string
    {
        return AcceptNotification::createHash($string, $algo);
    }

    /**
     * Receive and handle an instant payment notification (IPN)
     *
     * @return AcceptNotification
     */
    public function acceptNotification()
    {
        return new AcceptNotification($this->httpRequest, $this->getParameter('storeId'), $this->getParameter('sharedSecret'));
    }
}
