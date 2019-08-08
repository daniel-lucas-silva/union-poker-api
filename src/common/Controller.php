<?php

namespace App\Common;

use App\Models\Logs;
use App\ResponseException;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Firebase\JWT\JWT;
use Phalcon\Crypt\Mismatch;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\Model;

/**
 * Class Controller
 * @package App\Common
 * @property Mysql db
 * @property JWT jwt
 * @property array tokenConfig
 */
class Controller extends \Phalcon\Mvc\Controller {

  /**
   * @param array $body
   * @param array $fields
   * @throws ResponseException
   */
  protected function checkForEmptyData(array $body, array $fields)
  {
    $errors = [];
    foreach ($fields as $field) {
      if(!array_key_exists($field, $body))
        $errors[$field] = "validation.$field.REQUIRED";
    }

    if (count($errors)) {
      throw new ResponseException(400, 'common.INCOMPLETE_DATA_RECEIVED', $errors);
    }
  }

  /**
   * @throws ResponseException
   * @throws Exception
   */
  public function registerLog()
  {
    // gets user token
    $token_decoded = $this->decodeToken($this->getToken());
    // Gets URL route from request
    $url = $this->request->get();
    // Initiates log db transaction
    $this->db->begin();
    $newLog = new Logs();
    $newLog->email = $token_decoded->email; // gets username
    $newLog->route = $url['_url']; // gets route
    $newLog->date = $this->getNowDateTime();
    if (!$newLog->save()) {
      // rollback transaction
      $this->db->rollback();
      // Send errors
      $errors = array();
      foreach ($newLog->getMessages() as $message) {
        $errors[] = $message->getMessage();
      }

      throw new ResponseException(400, 'common.COULD_NOT_BE_CREATED', $errors);
    } else {
      // Commit the transaction
      $this->db->commit();
    }
  }

  /** Try to save data in DB
   * @param Model $element
   * @param string $customMessage
   * @return bool
   * @throws ResponseException
   */
  public function tryToSaveData($element, $customMessage = 'common.THERE_HAS_BEEN_AN_ERROR')
  {
    if (!$element->save()) {
      // Send errors
      $errors = array();
      foreach ($element->getMessages() as $message) {
        $errors[] = $message->getMessage();
      }
      throw new ResponseException(400, $customMessage, $errors);
    } else {
      return true;
    }
  }

  /** Try to delete data in DB
   * @param Model $element
   * @return void
   * @throws ResponseException
   */
  public function tryToDeleteData($element)
  {
    if (!$element->delete()) {
      // Send errors
      $errors = array();
      foreach ($element->getMessages() as $message) {
        $errors[] = $message->getMessage();
      }
      throw new ResponseException(400, 'common.COULD_NOT_BE_DELETED', $errors);
    }
  }

  /** Build options for listings
   * @param $defaultSort
   * @return array|mixed
   */
  public function buildOptions($defaultSort)
  {
    $options = [];
    $rows = 50;
    $order_by = $defaultSort;
    $offset = 0;
    $limit = $offset + $rows;
    // Handles Sort querystring (order_by)
    if ($this->request->get('sort') != null && $this->request->get('order') != null) {
      $order_by = $this->request->get('sort') . ' ' . $this->request->get('order');
    }
    // Gets rows_per_page
    if ($this->request->get('limit') != null) {
      $rows = $this->getQueryLimit($this->request->get('limit'));
      $limit = $rows;
    }
    // Calculate the offset and limit
    if ($this->request->get('offset') != null) {
      $offset = $this->request->get('offset');
      $limit = $rows;
    }
    $options = $this->array_push_assoc($options, 'rows', $rows);
    $options = $this->array_push_assoc($options, 'order_by', $order_by);
    $options = $this->array_push_assoc($options, 'offset', $offset);
    $options = $this->array_push_assoc($options, 'limit', $limit);
    return $options;
  }

  /** Build filters for listings
   * @param $filter
   * @return array
   */
  public function buildFilters($filter)
  {
    $filters = [];
    $conditions = [];
    $parameters = [];
    // Filters simple (no left joins needed)
    if ($filter != null) {
      $filter = json_decode($filter, true);
      foreach ($filter as $key => $value) {
        array_push($conditions, $key . ' LIKE :' . $key . ':');
        $parameters = $this->array_push_assoc($parameters, $key, '%' . trim($value) . '%');
      }
      $conditions = implode(' AND ', $conditions);
    }
    $filters = $this->array_push_assoc($filters, 'conditions', $conditions);
    $filters = $this->array_push_assoc($filters, 'parameters', $parameters);
    return $filters;
  }

  /** Build listing object
   * @param Model $elements
   * @param $rows
   * @param $total
   * @return array
   */
  public function buildListingObject($elements, $rows, $total)
  {
    $data = [];
    $data = $this->array_push_assoc($data, 'rows_per_page', $rows);
    $data = $this->array_push_assoc($data, 'total_rows', $total);
    $data = $this->array_push_assoc($data, 'rows', $elements->toArray());
    return $data;
  }

  /** Calculates total rows for an specified model
   * @param Model $model
   * @param $conditions
   * @param $parameters
   * @return mixed
   */
  public function calculateTotalElements($model, $conditions, $parameters, $cache = false)
  {
    $count = $cache ? 'countCache' : 'count';

    $total = $model::$count(
      array(
        $conditions,
        'bind' => $parameters,
      )
    );
    return $total;
  }

  /** Find element by ID from an specified model
   * @param $model
   * @param $id
   * @param bool $cache
   * @return mixed
   * @throws ResponseException
   */
  public function findElementById($model, $id, $cache = false)
  {
    $findFirst = $cache ? 'findFirstCache' : 'findFirst';

    $conditions = 'id = :id:';
    $parameters = array(
      'id' => $id,
    );
    $element = $model::$findFirst(
      array(
        $conditions,
        'bind' => $parameters,
      )
    );
    if (!$element) {
      throw new ResponseException(404, 'common.NOT_FOUND');
    }
    return $element;
  }

  /** Find elements from an specified model
   * @param $model
   * @param $conditions
   * @param $parameters
   * @param $columns
   * @param $order_by
   * @param $offset
   * @param $limit
   * @param bool $cache
   * @return mixed
   * @throws ResponseException
   */
  public function findElements($model, $conditions, $parameters, $columns, $order_by, $offset, $limit, $cache = false)
  {
    $find = $cache ? 'findCache' : 'find';

    $elements = $model::$find(
      array(
        $conditions,
        'bind' => $parameters,
        'columns' => $columns,
        'order' => $order_by,
        'offset' => $offset,
        'limit' => $limit,
      )
    );
    if (!$elements) {
      throw new ResponseException( 404, 'common.NO_RECORDS');
    }
    return $elements;
  }

  /** unset a properties from an array
   * @param $array
   * @param $remove
   * @return mixed
   */
  public function unsetPropertyFromArray($array, $remove)
  {
    foreach ($remove as $value) {
      unset($array[$value]);
    }
    return $array;
  }

  /** Generated NOW datetime based on a timezone
   * @return DateTime|string
   * @throws Exception
   */
  public function getNowDateTime()
  {
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('UTC'));
    $now = $now->format('Y-m-d H:i:s');
    return $now;
  }

  /** Generated NOW datetime based on a timezone and added XX minutes
   * @param $minutes_to_add
   * @return DateTime|string
   * @throws Exception
   */
  public function getNowDateTimePlusMinutes($minutes_to_add)
  {
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('UTC'));
    $now->add(new DateInterval('PT' . $minutes_to_add . 'M'));
    $now = $now->format('Y-m-d H:i:s');
    return $now;
  }

  /** Converts ISO8601 date to DateTime UTC
   * @param $date
   * @return false|string
   */
  public function iso8601_to_utc($date)
  {
    return $datetime = date('Y-m-d H:i:s', strtotime($date));
  }

  /** Converts DateTime UTC date to ISO8601
   * @param $date
   * @return string|null
   * @throws Exception
   */
  public function utc_to_iso8601($date)
  {
    if (!empty($date) && ($date != '0000-00-00') && ($date != '0000-00-00 00:00') && ($date != '0000-00-00 00:00:00')) {
      $datetime = new DateTime($date);
      return $datetime->format('Y-m-d\TH:i:s\Z');
    } else {
      return null;
    }
  }

  /** Array push associative.
   * @param $array
   * @param $key
   * @param $value
   * @return mixed
   */
  public function array_push_assoc($array, $key, $value)
  {
    $array[$key] = $value;
    return $array;
  }

  /** Generates limits for queries.
   * @param $limit
   * @return int
   */
  public function getQueryLimit($limit)
  {
    $setLimit = null;

    if ($limit != '') {
      if ($limit > 150) {
        $setLimit = 150;
      }
      if ($limit <= 0) {
        $setLimit = 1;
      }
      if (($limit >= 1) && ($limit <= 150)) {
        $setLimit = $limit;
      }
    } else {
      $setLimit = 5;
    }
    return $setLimit;
  }

  /**
   * Verifies if is get request
   */
  public function initializeGet()
  {
    if (!$this->request->isGet()) {
      die();
    }
  }

  /**
   * Verifies if is post request
   */
  public function initializePost()
  {
    if (!$this->request->isPost()) {
      die();
    }
  }

  /**
   * Verifies if is patch request
   */
  public function initializePatch()
  {
    if (!$this->request->isPatch()) {
      die();
    }
  }

  /**
   * Verifies if is patch request
   */
  public function initializeDelete()
  {
    if (!$this->request->isDelete()) {
      die();
    }
  }

  /** Encode token.
   * @param $data
   * @return mixed
   */
  public function encodeToken($data)
  {
    // Encode token
    $token_encoded = $this->jwt->encode($data, $this->tokenConfig['secret']);
    $token_encoded = $this->crypt->encryptBase64($token_encoded);
    return $token_encoded;
  }

  /** Decode token.
   * @param $token
   * @return object
   * @throws Mismatch
   */
  public function decodeToken($token)
  {
    // Decode token
    $token = $this->crypt->decryptBase64($token);
    $token = $this->jwt->decode($token, $this->tokenConfig['secret'], array('HS256'));
    return $token;
  }

  /**
   * Returns token from the request.
   * Uses token URL query field, or Authorization header
   */
  public function getToken()
  {
    $authHeader = $this->request->getHeader('Authorization');
    $authQuery = $this->request->getQuery('token');
    return $authQuery ? $authQuery : $this->parseBearerValue($authHeader);
  }

  /**
   * @param $string
   * @return string|string[]|null
   */
  protected function parseBearerValue($string)
  {
    if (strpos(trim($string), 'Bearer') !== 0) {
      return null;
    }
    return preg_replace('/.*\s/', '', $string);
  }

  /** Builds success responses.
   * @param $code
   * @param $messages
   * @param string $data
   */
  public function buildSuccessResponse($code, $messages, $data = '')
  {
    $status = null;

    switch ($code) {
      case 200:
        $status = 'OK';
        break;
      case 201:
        $status = 'Created';
        break;
      case 202:
        break;
    }
    $generated = array(
      'status' => $status,
      'code' => $code,
      'messages' => $messages,
      'data' => $data,
    );
    $this->response->setStatusCode($code, $status)->sendHeaders();
    $this->response->setContentType('application/json', 'UTF-8');
    $this->response->setJsonContent($generated, JSON_NUMERIC_CHECK)->send();
    die();
  }

  /** Builds error responses.
   * @param $code
   * @param $messages
   * @param string $data
   */
  public function buildErrorResponse($code, $messages, $data = '')
  {
    $status = null;

    switch ($code) {
      case 400:
        $status = 'Bad Request';
        break;
      case 401:
        $status = 'Unauthorized';
        break;
      case 403:
        $status = 'Forbidden';
        break;
      case 404:
        $status = 'Not Found';
        break;
      case 409:
        $status = 'Conflict';
        break;
    }
    $generated = array(
      'status' => $status,
      'code' => $code,
      'messages' => $messages,
      'data' => $data,
    );
    $this->response->setStatusCode($code, $status)->sendHeaders();
    $this->response->setContentType('application/json', 'UTF-8');
    $this->response->setJsonContent($generated, JSON_NUMERIC_CHECK)->send();
    die();
  }
}