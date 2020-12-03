<?php
namespace Omnipay\FirstDataConnectIPN;

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
     * Receive and handle an instant payment notification (IPN)
     *
     */
    public function acceptNotification()
    {
        return new Message\AcceptNotification($this->httpRequest, $this->getParameter('storeId'), $this->getParameter('sharedSecret'));
    }
}
