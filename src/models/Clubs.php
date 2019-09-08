<?php

namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Clubs
 * @package App\Models
 */
class Clubs extends CacheableModel
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
        self::$key = 'clubs';
        $this->setConnectionService('db'); // Connection service for log database
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return 'clubs';
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