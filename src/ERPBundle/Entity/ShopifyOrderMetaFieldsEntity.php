<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyOrderMetaFieldsEntity
 * @package ERPBundle\Entity
 */
class ShopifyOrderMetaFieldsEntity
{

    const ERPID = 'erp-id';

    public $erpOrderId;

    /**
     * @return mixed
     */
    public function getErpOrderId()
    {
        return $this->erpOrderId;
    }

    /**
     * @param $orderMetaFields
     * @return ShopifyOrderMetaFieldsEntity
     */
    public static function createFromResponse($orderMetaFields)
    {
        $self = new self();

        foreach($orderMetaFields['metafields'] as $metaField) {
            if($metaField['key'] == self::ERPID) {
                $self->erpOrderId = (int) $metaField['value'];
            }
        }

        return $self;
    }

}
