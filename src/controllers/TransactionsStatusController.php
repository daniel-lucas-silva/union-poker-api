<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\TransactionsStatus;
use App\ResponseException;
use Exception;

/**
 * Class TransactionsStatusController
 * @package App\Controllers
 */
class TransactionsStatusController extends Controller
{

  /**
   * @param $body
   * @return TransactionsStatus
   * @throws ResponseException
   */
  private function createStatus(array $body)
  {
    $status = new TransactionsStatus();
    $status->name = trim($body['name']);
    $this->tryToSaveData($status, 'common.COULD_NOT_BE_CREATED');
    return $status;
  }

  /**
   * @param $status
   * @param $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateStatus($status, $body)
  {
    $status->name = trim($body['name']);
    $this->tryToSaveData($status, 'common.COULD_NOT_BE_UPDATED');
    return $status;
  }

  /**
   * Get status
   */
  public function all()
  {
    try {
      $this->initializeGet();
      $model = new TransactionsStatus();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $status = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id, name', $options['order_by'], $options['offset'], $options['limit'], true);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters'], true);
      $data = $this->buildListingObject($status, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Create a new status
   * @throws Exception
   */
  public function create()
  {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $status = $this->createStatus($rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $status->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Get a status
   * @param $id
   */
  public function get($id)
  {
    try {
      $this->initializeGet();
      $model = new TransactionsStatus();
      $status = $this->findElementById($model, $id);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $status->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Update a status
   * @param $id
   * @throws Exception
   */
  public function update($id)
  {
    try {
      $this->initializePatch();
      $model = new TransactionsStatus();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $status = $this->updateStatus($this->findElementById($model, $id), $rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $status->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      $this->buildErrorResponse(444, $e->getMessage());
    }
  }

  /** Delete a status
   * @param $id
   * @throws Exception
   */
  public function delete($id)
  {
    try {
      $this->initializeDelete();
      $model = new TransactionsStatus();
      $this->tryToDeleteData($this->findElementById($model, $id));
//      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }
}