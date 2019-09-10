<?php

namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Transactions
 * @package App\Models
 */
class Transactions extends CacheableModel
{
    public $id;
    public $player_id;
    public $operator_id;
    public $bank_id;
    public $club_id;
    public $status_id;
    public $value;
    public $type;
    public $created_at;
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        self::$key = 'transactions';
        $this->setConnectionService('db');

        $this->belongsTo(
            'player_id', 'Players', 'id',
            ['alias' => 'player']
        );

        $this->belongsTo(
            'operator_id', 'Users', 'id',
            ['alias' => 'operator']
        );

        $this->belongsTo(
            'bank_id', 'Banks', 'id',
            ['alias' => 'bank']
        );

        $this->belongsTo(
            'club_id', 'Clubs', 'id',
            ['alias' => 'club']
        );
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return 'transactions';
    }

    /**
     * @return array
     */
    public function columnMap()
    {
        return [
            'id' => 'id',
            'player_id' => 'player_id',
            'operator_id' => 'operator_id',
            'bank_id' => 'bank_id',
            'club_id' => 'club_id',
            'status_id' => 'status_id',
            'value' => 'value',
            'type' => 'type',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }
}