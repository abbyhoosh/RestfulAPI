<?php
declare(strict_types=1);
namespace App\Controller;

/**
 * Tags Controller
 * 
 * @property \App\Model\Table\TagsTable $Tags
 * @method \App\Model\Entity\Tag[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TagsController extends AppController
{
    public function index() {
        $tags = $this->paginate($this->Tags);
        $this->set(compact('tags'));
        $this->set('_serialize', ['tags']);
    }

    public function view($id = null) {
        $tag = $this->Tags->get($id, ['contain' => ['Articles']]);
        $this->set(compact('tag'));
        $this->set('_serialize', ['tag']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add() {
        $tag = $this->Tags->newEmptyEntity();
        $message = 'tag was not made';
      
        if ($this->request->is('post')) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
          
            if ($this->Tags->save($tag)) {
                $message = 'tag was made';
            }
            $articles = $this->Tags->Articles->find('list', ['limit' => 200])->all();
            $this->set(compact('tag', 'articles'));
            $this->set(['message' => $message,'_serialize'=> ['tag', 'message']]);
        }
    }

    /**
     * Edit method
     *
     * @param string $id Tag id.
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id) {
        $tag = $this->Tags->get($id, ['contain' => ['Articles']]);
        $message= 'tag was not changed';
      
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
              
            if ($this->Tags->save($tag)) {
                $message= 'tag was changed';
            }
            $articles = $this->Tags->Articles->find('list', ['limit' => 200])->all();
            $this->set(compact('tag', 'articles'));
            $this->set(['message' => $message,'_serialize', ['tag', 'message']]);
        }
    }

    /**
     * Delete method
     *
     * @param string $id Tag id.
     * @return \Cake\Http\Response|null|void 
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete ($id) {
        $this->request->allowMethod(['post', 'delete']);
        $tag = $this->Tags->get($id);
        $message = 'tag was not deleted';
      
        if ($this->Tags->delete($tag)) {
            $message = 'tag was deleted';
        }
        $this->set(['message' => $message,'_serialize' => ['message']]);
    }
}
