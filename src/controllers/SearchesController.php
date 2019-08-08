<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Searches;
use App\ResponseException;
use Exception;

class SearchesController extends Controller
{

  /**
   * @param $body
   * @return Searches
   * @throws ResponseException
   */
  private function createSearch(array $body)
  {
    $search = new Searches();
    $search->name = trim($body['name']);
    $this->tryToSaveData($search, 'common.COULD_NOT_BE_CREATED');
    return $search;
  }

  /**
   * @param $search
   * @param $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateSearch($search, $body)
  {
    $search->name = trim($body['name']);
    $this->tryToSaveData($search, 'common.COULD_NOT_BE_UPDATED');
    return $search;
  }

  /**
   * Get searches
   */
  public function all()
  {
    try {
      $this->initializeGet();
      $model = new Searches();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $searches = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id, name', $options['order_by'], $options['offset'], $options['limit']);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters']);
      $data = $this->buildListingObject($searches, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Create a new search
   * @throws Exception
   */
  public function create()
  {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $search = $this->createSearch($rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $search->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Get a search
   * @param $id
   */
  public function get($id)
  {
    try {
      $this->initializeGet();
      $model = new Searches();
      $search = $this->findElementById($model, $id);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $search->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Update a search
   * @param $id
   * @throws Exception
   */
  public function update($id)
  {
    try {
      $this->initializePatch();
      $model = new Searches();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $search = $this->updateSearch($this->findElementById($model, $id), $rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $search->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      $this->buildErrorResponse(444, $e->getMessage());
    }
  }

  /** Delete a search
   * @param $id
   * @throws Exception
   */
  public function delete($id)
  {
    try {
      $this->initializeDelete();
      $model = new Searches();
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