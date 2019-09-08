<?php

namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Categories
 * @package App\Models
 */
class Banks extends CacheableModel
{
    public $id;
    public $ag;
    public $cc;
    public $name;
    public $type;
    public $balance;
    public $manager_name;
    public $manager_email;
    public $manager_phone;
    public $created_at;
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        self::$key = "banks";
        $this->setConnectionService('db'); // Connection service for log database
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return 'banks';
    }

    /**
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'ag' => 'ag',
            'cc' => 'cc',
            'name' => 'name',
            'type' => 'type',
            'balance' => 'balance',
            'manager_name' => 'manager_name',
            'manager_email' => 'manager_email',
            'manager_phone' => 'manager_phone',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}