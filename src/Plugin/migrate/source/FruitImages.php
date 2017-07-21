<?php
/**
 * @file
 * Contains \Drupal\fruit_migration\Plugin\migrate\source\FruitImages.
 */

namespace Drupal\fruit_migration\Plugin\migrate\source;

use Drupal\file\Plugin\migrate\source\d7\File;
use Drupal\Core\Database\Query\Condition;
use Drupal\migrate\Row;

/**
 * Import fruit images form d7
 *
 * @MigrateSource(
 *   id = "migrate_fruit_image"
 * )
 */
class FruitImages extends File {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'fm');
    $query->join('field_data_field_fruit_image', 'fi', 'fi.field_fruit_image_fid = fm.fid');
    $query->join('node', 'n', 'n.nid = fi.entity_id');
    $query->fields('fm', ['fid', 'uid', 'filename', 'uri', 'filemime', 'status', 'timestamp'])
      ->distinct()
      ->condition('fi.bundle', 'fruit')
      ->orderBy('n.changed', 'DESC');

    // Filter by scheme(s), if configured.
    if (isset($this->configuration['scheme'])) {
      $schemes = array();
      // Accept either a single scheme, or a list.
      foreach ((array) $this->configuration['scheme'] as $scheme) {
        $schemes[] = rtrim($scheme) . '://';
      }
      $schemes = array_map([$this->getDatabase(), 'escapeLike'], $schemes);

      // uri LIKE 'public://%' OR uri LIKE 'private://%'
      $conditions = new Condition('OR');
      foreach ($schemes as $scheme) {
        $conditions->condition('uri', $scheme . '%', 'LIKE');
      }
      $query->condition($conditions);
    }
    return $query;
  }
}