<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyTransactionEntity
 * @package ERPBundle\Entity
 */
class ShopifyTransactionEntity
{

    private $id;
    private $authorization;
    private $status;
    private $amount;
    private $currency;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param array $transaction
     * @return ShopifyTransactionEntity
     */
    public static function createFromOrderResponse(array $transaction)
    {
        $self = new self();
        $self->id = $transaction['id'];
        $self->authorization = $transaction['authorization'];
        $self->status = $transaction['status'];
        $self->amount = $transaction['amount'];
        $self->currency = $transaction['currency'];

        return $self;
    }

}
