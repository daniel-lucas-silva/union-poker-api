<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Clubs;
use App\ResponseException;
use Exception;

/**
 * Class ClubsController
 * @package App\Controllers
 */
class ClubsController extends Controller
{

  /**
   * @param $body
   * @return Clubs
   * @throws ResponseException
   */
  private function createClub(array $body)
  {
    $club = new Clubs();
    $club->name = trim($body['name']);
    $this->tryToSaveData($club, 'common.COULD_NOT_BE_CREATED');
    return $club;
  }

  /**
   * @param $club
   * @param $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateClub($club, $body)
  {
    $club->name = trim($body['name']);
    $this->tryToSaveData($club, 'common.COULD_NOT_BE_UPDATED');
    return $club;
  }

  /**
   * Get clubs
   */
  public function all()
  {
    try {
      $this->initializeGet();
      $model = new Clubs();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $clubs = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id, name', $options['order_by'], $options['offset'], $options['limit'], true);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters'], true);
      $data = $this->buildListingObject($clubs, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Create a new club
   * @throws Exception
   */
  public function create()
  {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $club = $this->createClub($rawBody);
      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $club->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Get a club
   * @param $id
   */
  public function get($id)
  {
    try {
      $this->initializeGet();
      $model = new Clubs();
      $club = $this->findElementById($model, $id);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $club->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Update a club
   * @param $id
   * @throws Exception
   */
  public function update($id)
  {
    try {
      $this->initializePatch();
      $model = new Clubs();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $club = $this->updateClub($this->findElementById($model, $id), $rawBody);
      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $club->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      $this->buildErrorResponse(444, $e->getMessage());
    }
  }

  /** Delete a club
   * @param $id
   * @throws Exception
   */
  public function delete($id)
  {
    try {
      $this->initializeDelete();
      $model = new Clubs();
      $this->tryToDeleteData($this->findElementById($model, $id));
      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }
}