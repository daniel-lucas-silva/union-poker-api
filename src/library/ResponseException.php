<?php
namespace App;

use Exception;

class ResponseException extends Exception
{
    private $_data = [];

    /**
     * ResponseException constructor.
     * @param integer $code
     * @param string $message
     * @param array $data
     */
    public function __construct($code, $message, $data = [])
    {
        $this->_data = $data;
        parent::__construct($message, $code);
    }

    public function getData()
    {
        return $this->_data;
    }

    public function toJson() {
        return [
            'code' => $this->code,
            'data' => $this->_data,
            'message' => $this->message,
        ];
    }
}