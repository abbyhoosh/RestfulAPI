<?php
declare(strict_types=1);

namespace App\Controller; 

use Firebase\JWT\JWT; //for generating the tokens
use Cake\Utility\Security;
use Cake\Event\Event;
use Cake\Http\Exception\UnauthorizedException;

class UsersController extends AppController {

  public function initialize(): void {
      parent::initialize();
      $this->Auth->allow(['add', 'token', 'view', 'index', 'me', 'login', 'logout']);
  }

  /**
   * isAuthorized method
   *
   * Returns whether a user is authorized to complete an attempted action
   * @param string $user User id
   * @return boolean.
   */
  public function isAuthorized($user) {
      // The owner of an article can edit and delete it
      if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {
          $userId = (int)$this->request->getParam('pass.0'); //should just be users id
          $requester = $this->Auth->user('id'); //person trying to complete the action
          if ($userId === $requester) {
              return true;
          }
      }
  }

  /**
   * Index method
   *
   * @return \Cake\Http\Response|null|void renders view.
   */
  public function index() {
      $users = $this->paginate($this->Users);
      $this->set(compact('users'));
      $this->set('_serialize', ['users']);
    }

   /**
     * View method
     *
     * Displays a specific user information
     * @param string $id User id
     * @return \Cake\Http\Response|null|void renders view otherwise.
     */
  public function view($id) {
      $user = $this->Users->get($id, ['contain' => ['Articles']]);
      $this->set(compact('user'));
      $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * creates a new user
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
  public function add() {
      $user = $this->Users->newEmptyEntity();
    
      if ($this->request->is('post')) {
          $user = $this->Users->patchEntity($user, $this->request->getData());
        
          if ($this->Users->save($user)) {
              //token generation
              $this->set('data', [
                         'id' => $user->id,
                         'token' => JWT::encode([
                                    'sub' => $user->id,
                                    'exp' =>  time() + 604800],
                                    Security::getSalt() )]);
          }
        }
      $this->set(compact('user'));
      $this->set('_serialize', ['user', 'data']);
  }

  /**
     * Edit method
     *
     * Edits an existing user
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
  public function edit($id) {
      $user = $this->Users->get($id, ['contain' => []]);
      $message = 'user was not updated';
    
      if ($this->request->is(['patch', 'post', 'put'])) {
          $user = $this->Users->patchEntity($user, $this->request->getData());
        
          if ($this->Users->save($user)) {
              $message = 'user was updated';
          }
      }
      $this->set(compact('user'));
      $this->set(['message' => $message, '_serialize'=> ['user', 'message']]);
  }

    /**
     * Delete method
     *
     * Deletes an existing user
     * @return \Cake\Http\Response|null|void Redirects on successful delete, renders view otherwise.
     */
  public function delete($id) {
      $message = 'user was not deleted';
      $this->request->allowMethod(['post', 'delete']);
      $user = $this->Users->get($id);
    
      if ($this->Users->delete($user)) {
          $message = 'user was deleted';
      } 
      this->set(['message' => $message,'serialize' => ['message']]);
  }

  /**
  * beforeFilter method
  *
  * @param \Cake\Event\EventInterface $event
  * @return void
  */
 public function beforeFilter(\Cake\Event\EventInterface $event) {
    parent::beforeFilter($event);
  }

  /**
  *login method
  *
  * find if user trying to login is an existing user
  * 
  * @return \Cake\Http\Response|null|void Redirects on successful login to articles index, renders view otherwise.
  * @throws \Cake\Datasource\Exception\UnauthorizedException When user not found.
  */
  public function login() {
      $this->request->allowMethod(['get', 'post']);
      $result = $this->Authentication->getResult();
    
      if ($result && $result->isValid()) {
          $message = 'user was logged in';
      }
    
      if ($this->request->is('post') && !$result->isValid()) {
          throw new UnauthorizedException('Invalid username or password');
      }
      this->set(['message' => $message,'serialize' => ['message']]);
  }

  /**
  *logout method
  *
  *@return redirect to login page
  */
  public function logout() {
    $result = $this->Authentication->getResult();
    if ($result && $result->isValid()) {
        $this->Authentication->logout();
    }
  }

  public function me() {
    $this->loadComponent('JsonResponse');
    return $this->JsonResponse->response([$this->Auth->user('id')]);
  }

  /**
  * token method
  *
  * Finds if user requesting a token exists and provides a token if they do
  * 
  * @return \Cake\Http\Response|null|void
  * @throws \Cake\Datasource\Exception\UnauthorizedException When user not found.
  */
  public function token() {
      $this->Auth->allow();
      $user = $this->Auth->identify();
    
      if (!$user) {
          throw new UnauthorizedException('Invalid username or password');
      }
      $this->set([
            'success' => true,
              'data' => [
                 'token' => JWT::encode([
                    'sub' => $user['id'],
                    'exp' =>  time() + 604800
                                        ],
                  Security::getSalt())
                 ],
            '_serialize' => ['success', 'data']
                 ]);
  }
}
