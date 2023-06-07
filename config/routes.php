<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {

    $routes->setRouteClass(DashedRoute::class);
    $routes->scope('/', function (RouteBuilder $builder) {
       $builder->setExtensions(['json']);
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');


      // New route we're adding for our tagged action.
      // The trailing `*` tells CakePHP that this action has
      // passed parameters.
        $builder->scope('/articles', function (RouteBuilder $builder) {
        $builder->connect('/tagged/*', ['controller' => 'Articles', 'action' => 'tags']);
        $builder->connect('/add', ['controller'=> 'Articles', 'action' => 'add']);    
    });

      $builder->scope('/tags', function(RouteBuilder $builder){
        $builder->connect('/add', ['controller' => 'tags', 'action'=>'add']);
      });
        /*
         * Connect catchall routes for all controllers.
         * The `fallbacks` method is a shortcut for
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', [])
         * You can remove these routes once you've connected the
         * routes you want in your application.
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
  
     * $routes->scope('/api', function (RouteBuilder $builder) {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */

  /**$routes->scope('/api', function (RouteBuilder $builder){
     $builder->setExtensions(['json']);
     $builder->resources('Articles');
     $builder->resources('Users');
     $builder->resources('Tags');
     $builder->connect('/users/token', ['controller' => 'Users', 'action' => 'token']);
    
  });*/

  
};
