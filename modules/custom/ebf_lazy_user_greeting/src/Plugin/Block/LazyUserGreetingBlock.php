<?php

namespace Drupal\ebf_lazy_user_greeting\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "ebf_lazy_user_greeting_block",
 *   admin_label = @Translation("Lazy user greeting (example)")
 * )
 */
final class LazyUserGreetingBlock extends BlockBase {

  public function build(): array {
    return [
      '#lazy_builder' => ['ebf_lazy_user_greeting.lazy_builder:buildGreeting', []],
      '#create_placeholder' => TRUE,
      '#cache' => [
        'max-age' => 3600,
      ],
    ];
  }

}
