<?php

namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class TransactionsStatus
 * @package App\Models
 */
class TransactionsStatus extends CacheableModel
{
    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        self::$key = 'transactions_status';
        $this->setConnectionService('db'); // Connection service for log database
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return 'transactions_status';
    }

    /**
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}