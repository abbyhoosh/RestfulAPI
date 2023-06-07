<?php
// src/Model/Entity/Article.php
namespace App\Model\Entity;
use Cake\ORM\Entity;
use Cake\Collection\Collection;

class Article extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'slug' => false,
        'tag_string'=> true,
    ];


  //for making a tag a computed variable that can be used and put inside of the tag values
  protected function _getTagString()
{
    if (isset($this->_fields['tag_string'])) { //if the field tags is set
        return $this->_fields['tag_string']; //return the field value
    }
    if (empty($this->tags)) { //if there are no tags
        return ''; //return an empty string
    }
    $tags = new Collection($this->tags);
    $str = $tags->reduce(function ($string, $tag) {
        return $string . $tag->title . ', ';
    }, '');
    return trim($str, ', ');
}
}