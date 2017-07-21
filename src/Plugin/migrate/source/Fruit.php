<?php
 
/**
 * @file
 * Contains \Drupal\fruit_migration\Plugin\migrate\source\Fruit.
 */
 
namespace Drupal\fruit_migration\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;
 
/**
 * Drupal 7
 *
 * @MigrateSource(
 *   id = "migrate_fruits"
 * )
 */
class Fruit extends Node {
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    
    //Body field with value, summary, and format
    $result = $this->select('field_data_body', 'fdb')
      ->fields('fdb', ['body_value', 'body_summary', 'body_format'])
      ->condition('fdb.entity_id', $nid)
      ->execute();
    while($record = $result->fetchObject()){
      $row->setSourceProperty('body_value', $record->body_value );
      $row->setSourceProperty('body_summary', $record->body_summary );
      $row->setSourceProperty('body_format', $record->body_format );
    }
    //Price field
    $field_price_value = $this->select('field_data_field_price', 'p')
      ->fields('p', ['field_price_value'])
      ->condition('p.entity_id', $nid)
      ->execute()->fetchField();
    if (!empty($field_price_value)) {
      $row->setSourceProperty('price', $field_price_value);
    }
    // Vitamin Terms Referrence
    $vitamin_terms = [];
    $result = $this->select('field_data_field_vitamins', 'fdv')
      ->fields('fdv', ['field_vitamins_tid'])
      ->condition('fdv.entity_id', $nid)
      ->execute();
    while($record = $result->fetchObject()){
      $vitamin_terms[] = $record->field_vitamins_tid;
    } 
    if (!empty($vitamin_terms)) {
        $row->setSourceProperty('vitamin_terms', $vitamin_terms);
    }
    // Image field
    $images = [];
    $result = $this->select('field_data_field_fruit_image', 'fdi')
      ->fields('fdi', ['field_fruit_image_fid', 'field_fruit_image_alt', 'field_fruit_image_title', 'field_fruit_image_width', 'field_fruit_image_height'])
      ->condition('fdi.entity_id', $nid)
      ->execute();
    while($record = $result->fetchObject()){
      $images[] = [
        'target_id' => $record->field_fruit_image_fid,
        'alt' => $record->field_fruit_image_alt,
        'title' => $record->field_fruit_image_title,
        'width' => $record->field_fruit_image_width,
        'height' => $record->field_fruit_image_height,
      ];
    }
    if (!empty($images)) {
        $row->setSourceProperty('fruit_images', $images);
    }
    // Migrate URL alias.
    $alias = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias'])
      ->condition('ua.source', 'node/' . $nid)
      ->execute()
      ->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', '/' . $alias);
    }
    return parent::prepareRow($row);
  }
}