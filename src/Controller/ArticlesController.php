<?php
// src/Controller/ArticlesController.php
namespace App\Controller;

class ArticlesController extends AppController {
  /**
     * isAuthorized method
     *
     * Returns whether a user is authorized to complete an attempted action
     * @param string $user User id
     * @return boolean.
     */
  public function isAuthorized($user) {
      // All registered users can add articles
      if ($this->request->getParam('action') === 'add') {
          return true;
      }
      // The owner of an article can edit and delete it
      if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {
          $slug = $this->request->getParam('pass.0'); //should just be article slug
          $article = $this->Articles //find article with slug
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();
        
          if ($this->Articles->isOwnedBy($article->id, $user['id'])) {
              return true;
          }
      }
    return parent::isAuthorized($user);
  }

  /**
   * Index method
   *
   * @return \Cake\Http\Response|null|void renders view.
   */
  public function index() {
      $this->loadComponent('Paginator');
      $articles = $this->Paginator->paginate($this->Articles->find());
      $this->set(compact('articles'));
      $this->set('_serialize', ['articles']);
  }

  /**
     * View method
     *
     * Displays a specific article
     * @param string $slug Article slug
     * @return \Cake\Http\Response|null|void renders view otherwise.
     */
  public function view($slug) {
      $article = $this->Articles
        ->findBySlug($slug)
        ->contain('Tags')
        ->firstOrFail();
      $this->set(compact('article'));
      $this->set('_serialize', ['article']);
  }

  /**
     * Add method
     *
     * creates a new article
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
  public function add() {
      $this->Auth->allow();
      $article = $this->Articles->newEmptyEntity();
      $message = 'your article was not posted';
    
      if ($this->request->is('post')) {
          $article = $this->Articles->patchEntity($article, $this->request->getData());
          $article->user_id = $this->Auth->user('id');
        
          if ($this->Articles->save($article)) {
              $message = 'your article is posted';
          }
      }
      // Get a list of tags.
      $tags = $this->Articles->Tags->find('list')->all();
      // Set tags to the view context
      $this->set(['tags' => $tags, 
                  'article' => $article, 
                  'message' => $message,
                  '_serialize' => [ 'article', 'message']
                ]);
  }

  /**
    * Edit method
    *
    * Edits an existing article
    * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
    */
  public function edit($slug) {
      $article = $this->Articles
        ->findBySlug($slug)
        ->contain('Tags') // load associated Tags
        ->firstOrFail();

    if ($this->request->is(['post', 'put'])) {
        $this->Articles->patchEntity($article, $this->request->getData(), [
            //Disable modification of user_id.
            'accessibleFields' => ['user_id' => false]
        ]);
    }
    //find list of tags
    $tags = $this->Articles->Tags->find('list')->all();
    $this->set(compact('article', 'tags'));
    $this->set('_serialize', ['article','message']);
  }

  /**
    * Delete method
    *
    * Deltes an existing article
    * @return \Cake\Http\Response|null|void Redirects on successful delete, renders view otherwise.
    */
  public function delete($slug) { 
      $this->request->allowMethod(['post', 'delete']);
      $article = $this->Articles->findBySlug($slug)->firstOrFail();
      $message= 'post was not deleted';
  
      if ($this->Articles->delete($article)) {
          $message = 'post was deleted';
      }
      $this->set(['message' => $message, '_serialize'=> ['message']]);
  }

  /**
    * Tags method
    *
    * Display articles with the tags searched for
    * @return \Cake\Http\Response|null|void renders view
    */
  public function tags() {
    // The 'pass' key is provided by CakePHP and contains all
    // the passed URL path segments in the request.
    $tags = $this->request->getParam('pass');
    $this->Auth->allow();
    // Use the ArticlesTable to find tagged articles.
    $articles = $this->Articles->find('tagged', ['tags' => $tags])->all();
    // Pass variables into the view template context.
    $this->set([
        'articles' => $articles,
        'tags' => $tags,
        '_serialize' => ['articles', 'tags']
    ]);
  }
}
