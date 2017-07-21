<?php
 
/**
 * @file
 * Contains \Drupal\migrate_custom\Plugin\migrate\source\Term.
 */
 
namespace Drupal\fruit_migration\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
 
/**
 * Import vitamins terms from Drupal 7
 *
 * @MigrateSource(
 *   id = "migrate_fruit_vitamins",
 * )
 */
class Vitamins extends SqlBase {
 
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('taxonomy_term_data', 'td');
    $query->join('taxonomy_vocabulary', 'tv', 'tv.vid = td.vid');
    $query->fields('td', array('tid', 'vid', 'name', 'description', 'weight', 'format'))
      ->distinct()
      ->condition('tv.machine_name', 'vitamins');
    return $query;
  }
 
  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'tid' => $this->t('The term ID.'),
      'vid' => $this->t('Existing term VID'),
      'name' => $this->t('The name of the term.'),
      'description' => $this->t('The term description.'),
      'weight' => $this->t('Weight'),
      'parent' => $this->t("The Drupal term IDs of the term's parents."),
    );
  }
 
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Find parents for this row.
    $parents = $this->select('taxonomy_term_hierarchy', 'th')
      ->fields('th', array('parent', 'tid'))
      ->condition('tid', $row->getSourceProperty('tid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('parent', $parents);
    $row->setSourceProperty('vocbulary_machine_name', 'vitamins');
    return parent::prepareRow($row);
  }
 
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['tid']['type'] = 'integer';
    return $ids;
  }
}