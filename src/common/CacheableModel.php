<?php

namespace App\Common;

/**
 * Class CacheableModel
 * @package App\Common
 */
class CacheableModel extends \Phalcon\Mvc\Model
{
  protected static $key;
  /*** Implement a method that returns a string key based
   * on the query parameters
   * @param $parameters
   * @return string
   */
  protected static function _createKey($parameters)
  {
    $uniqueKey = [];
    $uniqueKey[] = self::$key;

    foreach ($parameters as $key => $value) {
      if (is_scalar($value)) {
        $uniqueKey[] = $key . ':' . $value;
      } elseif (is_array($value)) {
        $uniqueKey[] = $key . ':[' . self::_createKey($value) . ']';
      }
    }

    return join(',', $uniqueKey);
  }

  /**
   * @param null $parameters
   * @return \Phalcon\Mvc\Model\ResultsetInterface
   */
  public static function findCache($parameters = null)
  {
    // Convert the parameters to an array
    if (!is_array($parameters)) {
      $parameters = [$parameters];
    }

    if (!isset($parameters['cache'])) {
      $parameters['cache'] = [
        'key'      => self::_createKey($parameters),
        'lifetime' => 100,
      ];
    }

    return parent::find($parameters);
  }

  /**
   * @param null $parameters
   * @return mixed
   */
  public static function countCache($parameters = null)
  {
    // Convert the parameters to an array
    if (!is_array($parameters)) {
      $parameters = [$parameters];
    }

    // Check if a cache key wasn't passed
    // and create the cache parameters
    if (!isset($parameters['cache'])) {
      $parameters['cache'] = [
        'key'      => self::_createKey($parameters),
        'lifetime' => 100,
      ];
    }

    return parent::count($parameters);
  }

  /**
   * @param null $parameters
   * @return \Phalcon\Mvc\Model
   */
  public static function findFirstCache($parameters = null)
  {
    if (!is_array($parameters)) {
      $parameters = [$parameters];
    }
    if (!isset($parameters['cache'])) {
      $parameters['cache'] = [
        'key'      => self::_createKey($parameters),
        'lifetime' => 100,
      ];
    }

    return parent::findFirst($parameters);
  }
}