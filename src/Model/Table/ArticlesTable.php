<?php
// src/Model/Table/ArticlesTable.php
namespace App\Model\Table;
use Cake\ORM\Query;
//allow a validator
use Cake\Validation\Validator;
//table preset
use Cake\ORM\Table;
// the Text class
use Cake\Utility\Text;
// the EventInterface class
use Cake\Event\EventInterface;

class ArticlesTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        $this->addBehavior('Timestamp');
        $this->belongsToMany('Tags', [
        'joinTable' => 'articles_tags', 
        'dependent' => true //dependent makes it so if a article is delted to delete from the join table
        ]);
    }

  /**
  * beforeSave method
  *
  * before the save the method creates a slug if the object is new
  *
  * @param Eventinterface $event
  * @param Eventinterface $entity
  * @param Eventinterface $options
  * @return void
  */
  public function beforeSave(EventInterface $event, $entity, $options) {
      if ($entity->tag_string) { //if there is string put in by user for tags it creates those tags
          $entity->tags = $this->_buildTags($entity->tag_string);
      }
  
      if ($entity->isNew() && !$entity->slug) {
          $sluggedTitle = Text::slug($entity->title);
          // trim slug to maximum length defined in schema
          $entity->slug = substr($sluggedTitle, 0, 191);
      }
  }

  /**
  * _buildTags method
  *
  * creates an array of the tags a user input and adds new ones to the Tags table
  *
  * @param string $tagString
  * @return string[] $out
  *
  */
  protected function _buildTags($tagString) {
    // Trim tags, array_map() is like .toArray() returns an array of the strings after being split
    $newTags = array_map('trim', explode(',', $tagString)); //explode is like split, breaks up the string based on ,
    // Remove all empty tags
    $newTags = array_filter($newTags);
    // Reduce duplicated tags
    $newTags = array_unique($newTags);
    
    //goes into already stored tags and and gets all tag values that are present in the $newTags array
    $out = [];
    $tags = $this->Tags->find()
        ->where(['Tags.title IN' => $newTags])
        ->all();

    // Remove existing tags from the list of new tags.
    foreach ($tags->extract('title') as $existing) { //as $existing is like (for numbers: n) n-2;
        $index = array_search($existing, $newTags); //goes through each exisiting tag and returns that value from the array
        if ($index !== false) { //once it finds the index
            unset($newTags[$index]); //remove value from the array
        }
    }
    // Add existing tags.
    foreach ($tags as $tag) {
        $out[] = $tag; //re-add already existing tags
    }
    // Add new tags.
    foreach ($newTags as $tag) {
        $out[] = $this->Tags->newEntity(['title' => $tag]);  //creates new tag in table
    }
    return $out; //outputs all tags (still shows value inserted but doesn't add it to the table)
}

  /**
  * validationDefault
  *
  * Checks if the user input meets the standards set
  *
  * @param Validator $validator
  * @return Validator $valdator
  */
  public function validationDefault(Validator $validator): Validator {
      $validator
          ->notEmptyString('title')
          ->minLength('title', 10)
          ->maxLength('title', 255)
  
          ->notEmptyString('body')
          ->minLength('body', 10);
  
      return $validator;
  }

  // The $query argument is a query builder instance.
  // The $options array will contain the 'tags' option we passed
  // to find('tagged') in our controller action.
  public function findTagged(Query $query, array $options) {
      $columns = [
        'Articles.id', 'Articles.user_id', 'Articles.title',
        'Articles.body', 'Articles.published', 'Articles.created',
        'Articles.slug',
    ];

    $query = $query
        ->select($columns)
        ->distinct($columns);

    if (empty($options['tags'])) {
        // If there are no tags provided, find articles that have no tags.
        $query->leftJoinWith('Tags')
              ->where(['Tags.title IS' => null]);
    } else {
        // Find articles that have one or more of the provided tags.
        $query->innerJoinWith('Tags')
              ->where(['Tags.title IN' => $options['tags']]);
    }
    return $query->group(['Articles.id']);
  }

  /**
  * isOwnedBy method
  *
  * finds if there exists an article with the given id and user id
  *
  * @param int $articleId Article id
  * @param int $userId User id
  * @return boolean
  */
  public function isOwnedBy($articleId, $userId) {
      return $this->exists(['id' => $articleId, 'user_id' => $userId]);
  }
}