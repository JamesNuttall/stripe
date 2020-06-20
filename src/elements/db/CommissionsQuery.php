<?php
/**
 * Stripe Payments plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2018 Enupal LLC
 */

namespace enupal\stripe\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class CommissionsQuery extends ElementQuery
{
    // General - Properties
    // =========================================================================
    public $id;
    public $orderId;
    public $connectId;
    public $orderType;
    public $commissionStatus;
    public $totalPrice;
    public $currency;
    public $datePaid;

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function orderId($value)
    {
        $this->orderId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->connectId;
    }

    /**
     * @inheritdoc
     */
    public function connectId($value)
    {
        $this->connectId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConnectId()
    {
        return $this->connectId;
    }

    /**
     * @inheritdoc
     */
    public function orderType($value)
    {
        $this->orderType = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @inheritdoc
     */
    public function commissionStatus($value)
    {
        $this->commissionStatus = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCommissionStatus()
    {
        return $this->commissionStatus;
    }

    /**
     * @inheritdoc
     */
    public function totalPrice($value)
    {
        $this->totalPrice = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @inheritdoc
     */
    public function currency($value)
    {
        $this->currency = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function datePaid($value)
    {
        $this->datePaid = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDatePaid()
    {
        return $this->datePaid;
    }

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'enupalstripe_commissions.dateCreated';
        }

        parent::__construct($elementType, $config);
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('enupalstripe_commissions');

        if (is_null($this->query)){
            return false;
        }

        $this->query->select([
            'enupalstripe_commissions.id',
            'enupalstripe_commissions.connectId',
            'enupalstripe_commissions.orderId',
            'enupalstripe_commissions.orderType',
            'enupalstripe_commissions.commissionStatus',
            'enupalstripe_commissions.totalPrice',
            'enupalstripe_commissions.currency',
            'enupalstripe_commissions.datePaid'
        ]);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.id', $this->id)
            );
        }

        if ($this->connectId) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.connectId', $this->connectId)
            );
        }

        if ($this->orderId) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.orderId', $this->orderId)
            );
        }

        if ($this->orderType) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.orderType', $this->orderType)
            );
        }

        if ($this->commissionStatus) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.commissionStatus', $this->commissionStatus)
            );
        }

        if ($this->totalPrice !== null) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.totalPrice', $this->totalPrice)
            );
        }

        if ($this->currency !== null) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.currency', $this->currency)
            );
        }

        if ($this->datePaid !== null) {
            $this->subQuery->andWhere(Db::parseParam(
                'enupalstripe_commissions.datePaid', $this->datePaid)
            );
        }

        if ($this->dateCreated) {
            $this->subQuery->andWhere(Db::parseDateParam('enupalstripe_commissions.dateCreated', $this->dateCreated));
        }


        if ($this->orderBy !== null && empty($this->orderBy) && !$this->structureId && !$this->fixedOrder) {
            $this->orderBy = 'elements.dateCreated desc';
        }

        return parent::beforePrepare();
    }
}