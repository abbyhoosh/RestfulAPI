<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\View\JsonView;

class AppController extends Controller {
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
  
  public function initialize(): void {
      parent::initialize();
      $this->loadComponent('RequestHandler');
      $this->loadComponent('Flash');
      $this->loadComponent('Auth', [
                           'authorize' => ['Controller'], // Authorize certain users
                           'storage' => 'Memory',
                           'authenticate' => [
                             'Form' =>[
                               'userModel' => 'Users',
                                'fields' => [
                                  'username' => 'email',
                                  'password' => 'password'
                                ]
                             ],
                             'ADmad/JwtAuth.Jwt' => [
                             'userModel' => 'Users',
                             'fields' => [
                             'username' => 'id'
                             ],
                            'parameter' => 'token',
                            // Boolean indicating whether the "sub" claim of JWT payload
                            // should be used to query the Users model and get user info.
                            // If set to `false` JWT's payload is directly returned.
                            'queryDatasource' => true,
                             ]
                           ],
                           'unauthorizedRedirect' => false,
                           'checkAuthIn' => 'Controller.initialize',
                          'loginAction' => 'api/users/login',
                           ]);
  }
  
  public function beforeFilter(\Cake\Event\EventInterface $event) {
      parent::beforeFilter($event);
  }
    
  public function isAuthorized($user) {
    return true;
  }

}
