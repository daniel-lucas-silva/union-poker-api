<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Listings;
use App\Models\Users;
use App\Models\UserAccess;
use App\ResponseException;
use Exception;
use Phalcon\Mvc\Model;

/**
 * Class UsersController
 * @package App\Controllers
 */
class UsersController extends Controller {

  /**
   * @param string $username
   */
  private function checkForbiddenUsername($username)
  {
    $username = trim($username);
    if ($username == 'admin') {
      $this->buildErrorResponse(409, 'common.COULD_NOT_BE_CREATED');
    }
  }

  /**
   * @param array $body
   * @return Users
   * @throws ResponseException
   */
  private function createUser(array $body)
  {
    $user = new Users();
    $user->email = trim($body['email']);
    $user->username = trim($body['username']);
    $user->name = trim($body['name']);
    $user->role = trim($body['type']);
    $user->active = 1;
    $user->password = password_hash($body['password'], PASSWORD_BCRYPT);
    $this->tryToSaveData($user, 'common.COULD_NOT_BE_CREATED');
    return $user;
  }

  /**
   * @param $user
   * @return mixed
   * @throws Exception
   */
  private function findUserLastAccess($user)
  {
    $conditions = 'username = :username:';
    $parameters = [
      'username' => $user['username'],
    ];
    $last_access = UserAccess::find([
      $conditions,
      'bind' => $parameters,
      'columns' => 'date, ip, domain, browser',
      'order' => 'id DESC',
      'limit' => 10,
    ]);
    if ($last_access) {
      $array = [];
      $user_last_access = $last_access->toArray();
      foreach ($user_last_access as $key_last_access => $value_last_access) {
        $this_user_last_access = [
          'date' => $this->utc_to_iso8601($value_last_access['date']),
          'ip' => $value_last_access['ip'],
          'domain' => $value_last_access['domain'],
          'browser' => $value_last_access['browser'],
        ];
        $array[] = $this_user_last_access;
      }
      $user = empty($array) ? $this->array_push_assoc($user, 'last_access', '') : $this->array_push_assoc($user, 'last_access', $array);
      return $user;
    }
    return false;
  }

  /**
   * @param $user
   * @param array $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateUser($user, array $body)
  {
    $user->email = trim($body['email']);
    $user->username = trim($body['username']);
    $user->name = trim($body['name']);
    $user->role = trim($body['role']);
    $user->active = trim($body['active']);
    $this->tryToSaveData($user, 'common.COULD_NOT_BE_UPDATED');
    return $user;
  }

  /**
   * @param $user
   * @throws ResponseException
   */
  private function setNewPassword(Users $user)
  {
    $user->password = password_hash($this->request->getPut('new_password'), PASSWORD_BCRYPT);
    $this->tryToSaveData($user, 'common.COULD_NOT_BE_UPDATED');
  }

  /**
   * @param $headers
   * @return bool
   * @throws ResponseException
   */
  private function checkIfHeadersExist($headers)
  {
    if(!(!isset($headers['Authorization']) || empty($headers['Authorization'])))
      return true;

    throw new ResponseException(403, 'common.HEADER_AUTHORIZATION_NOT_SENT');
  }

  /**
   * @param array $credentials
   * @return Users|Model
   * @throws ResponseException
   */
  private function findUser(array $credentials)
  {
    $identity = $credentials['username'];
    $conditions = 'username = :identity: OR email = :identity:';
    $parameters = [
      'identity' => $identity,
    ];
    $user = Users::findFirst([
      $conditions,
      'bind' => $parameters
    ]);
    if ($user) return $user;

    throw new ResponseException(404, 'users.USER_IS_NOT_REGISTERED');
  }

  /**
   * @param $credentials
   * @return mixed
   */
  private function getUserPassword($credentials)
  {
    return $credentials['password'];
  }

  /**
   * @param $user
   * @return bool
   * @throws ResponseException
   * @throws Exception
   */
  private function checkIfUserIsNotBlocked($user)
  {
    $block_expires = strtotime($user->block_expires);
    $now = strtotime($this->getNowDateTime());

    if($block_expires > $now)
      throw new ResponseException(403, 'users.USER_BLOCKED');
    else
      return true;
  }

  /**
   * @param $user
   * @return bool|void
   */
  private function checkIfUserIsAuthorized($user)
  {
    return ($user->active == 0) ? $this->buildErrorResponse(403, 'users.USER_UNAUTHORIZED') : true;
  }

  /**
   * @param $user
   * @return int
   * @throws ResponseException
   */
  private function addOneLoginAttempt($user)
  {
    $user->login_attempts = $user->login_attempts + 1;
    $this->tryToSaveData($user);
    return $user->login_attempts;
  }

  /**
   * @param $minutes
   * @param $user
   * @throws ResponseException
   * @throws Exception
   */
  private function addXMinutesBlockToUser($minutes, $user)
  {
    $user->block_expires = $this->getNowDateTimePlusMinutes($minutes);
    if ($this->tryToSaveData($user)) {
      $this->buildErrorResponse(400, 'users.TOO_MANY_FAILED_LOGIN_ATTEMPTS');
    }
  }

  /**
   * @param $password
   * @param $user
   * @throws ResponseException
   */
  private function checkPassword($password, $user)
  {
    if (!password_verify($password, $user->password)) {
      $login_attempts = $this->addOneLoginAttempt($user);
      if($login_attempts <= 4)
        throw new ResponseException(400, 'users.WRONG_USER_PASSWORD');
      else
        $this->addXMinutesBlockToUser(120, $user);
    }
  }

  /**
   * @param $password
   * @param $user
   * @throws ResponseException
   */
  private function checkIfPasswordNeedsRehash($password, $user)
  {
    $options = [
      'cost' => 10, // the default cost is 10, max is 12.
    ];
    if (password_needs_rehash($user->password, PASSWORD_DEFAULT, $options)) {
      $newHash = password_hash($password, PASSWORD_DEFAULT, $options);
      $user->password = $newHash;
      $this->tryToSaveData($user);
    }
  }

  /**
   * @param $user
   * @return array
   */
  private function buildUserData($user)
  {
    $user_data = [
      'id'        => $user->id,
      'username'  => $user->username,
      'email'     => $user->email,
      'name'      => $user->name,
      'avatar'    => $user->avatar,
      'role'      => $user->role,
    ];
    return $user_data;
  }

  /**
   * @param $user
   * @return array
   * @throws Exception
   */
  private function buildTokenData(Users $user)
  {
    // issue at time and expires (token)
    $iat = strtotime($this->getNowDateTime());
    $exp = strtotime('+' . $this->tokenConfig['expiration_time'] . ' seconds', $iat);
    $token_data = [
      'iss' => $this->tokenConfig['iss'],
      'aud' => $this->tokenConfig['aud'],
      'iat' => $iat,
      'exp' => $exp,
      'uid' => $user->id,
      'username' => $user->username,
      'email' => $user->email,
      'role' => $user->role,
      'rand' => rand() . microtime()
    ];
    return $token_data;
  }

  /**
   * @param $user
   * @throws ResponseException
   */
  private function resetLoginAttempts($user)
  {
    $user->login_attempts = 0;
    $this->tryToSaveData($user);
  }

  /**
   * @param $user
   * @throws ResponseException
   * @throws Exception
   */
  private function registerNewUserAccess(Users $user)
  {
    $headers = $this->request->getHeaders();
    $newAccess = new UserAccess();
    $newAccess->email = $user->email;
    $newAccess->ip = (isset($headers['Http-Client-Ip']) || !empty($headers['Http-Client-Ip'])) ? $headers['Http-Client-Ip'] : $this->request->getClientAddress();
    $newAccess->platform = $this->request->getUserAgent();
    $newAccess->date = $this->getNowDateTime();
    $this->tryToSaveData($newAccess);
  }

  public function me()
  {
    try {
      $this->initializeGet();

      $this->checkIfHeadersExist($this->request->getHeaders());

      $user = $this->findUser((array) $this->decodeToken($this->getToken()));
      $user_data = $this->buildUserData($user);
      $token = $this->encodeToken($this->buildTokenData($user));

      $data = [
        'token' => $token,
        'user' => $user_data
      ];

      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function login()
  {
    try {
      $this->initializePost();

      $this->checkIfHeadersExist($this->request->getHeaders());

      $user = $this->findUser($this->request->getBasicAuth());
      $password = $this->getUserPassword($this->request->getBasicAuth());
      $this->checkIfUserIsNotBlocked($user);
      $this->checkIfUserIsAuthorized($user);
      $this->checkPassword($password, $user);
      // ALL OK, proceed to login
      $this->checkIfPasswordNeedsRehash($password, $user);
      $user_data = $this->buildUserData($user);
      $token = $this->encodeToken($this->buildTokenData($user));
      $data = [
        'token' => $token,
        'user' => $user_data
      ];

      $this->resetLoginAttempts($user);
      $this->registerNewUserAccess($user);

      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /**
   * Register a new user
   */
  public function register() {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $rawBody['type'] = 'user'; // default role
      $this->checkForEmptyData($rawBody, ['email', 'username', 'name', 'password']);
      $this->checkForbiddenUsername($rawBody['username']);
      $user = $this->createUser($rawBody);

      $user_data = $this->buildUserData($user);
      $token = $this->encodeToken($this->buildTokenData($user));
      $data = [
        'token' => $token,
        'user' => $user_data
      ];

      $this->resetLoginAttempts($user);
      $this->registerNewUserAccess($user);
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /**
   * Create a new user
   */
  public function create() {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['email', 'username', 'name', 'password']);
      // TODO: define logic for roles
      $rawBody['role'] = 'user';
      $this->checkForbiddenUsername($rawBody['username']);
      $user = $this->createUser($rawBody);
      $user = $user->toArray();
      $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $user);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function all() {
    try {
      $this->initializeGet();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $model = new Users();
      $users = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id,email,name,username,avatar', $options['order_by'], $options['offset'], $options['limit']);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters']);
      $data = $this->buildListingObject($users, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function get($id) {
    try {
      $this->initializeGet();
      $model = new Users();
      $user = $this->findElementById($model, $id);
      $user = $user->toArray();
      $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
      $user = $this->findUserLastAccess($user);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $user);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function update($id) {
    try {
      $this->initializePatch();
      $model = new Users();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name', 'active']);
      $user = $this->updateUser($this->findElementById($model, $id), $rawBody);
      $user = $user->toArray();
      $user = $this->unsetPropertyFromArray($user, ['password', 'block_expires', 'login_attempts']);
      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $user);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function changePassword() {
    try {
      $this->initializePatch();
      $model = new Users();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['identity']);
//      $user = $this->findElementById($model, $id); // find element by identity
      $this->registerLog();
      $this->buildSuccessResponse(200, 'users.PASSWORD_CHANGE_REQUESTED');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function resetPassword($id) {
    try {
      $this->initializePatch();
      $model = new Users();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['new_password']);
      $user = $this->findElementById($model, $id);
      $this->setNewPassword($user);
      $this->registerLog();
      $this->buildSuccessResponse(200, 'users.PASSWORD_SUCCESSFULLY_UPDATED');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  public function delete($id) {
    try {
      $this->initializeDelete();
      $model = new Users();
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