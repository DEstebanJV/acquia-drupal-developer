<?php

namespace Drupal\ebf_lazy_user_greeting\LazyBuilders;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Security\TrustedCallbackInterface;


final class UserGreetingLazyBuilder  implements TrustedCallbackInterface {

  public static function trustedCallbacks(): array {
    return ['buildGreeting'];
  }
  
  public function __construct(
    private readonly AccountProxyInterface $currentUser,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public function buildGreeting(): array {

    if ($this->currentUser->isAnonymous()) {
      return [
        '#markup' => t('Hola visitante 👋 (inicia sesión para ver tu saludo).'),
        '#cache' => [
          'contexts' => ['user'],
        ],
      ];
    }

    $user = $this->entityTypeManager
      ->getStorage('user')
      ->load($this->currentUser->id());

    $name = $user->getDisplayName();
    $roles = $user->getRoles();
    $roles_text = implode(', ', $roles);

    return [
      '#type' => 'container',
      'title' => [
        '#markup' => '<strong>' . t('Hola @name 👋', ['@name' => $name]) . '</strong>',
      ],
      'roles' => [
        '#markup' => '<div>' . t('Tus roles: @roles', ['@roles' => $roles_text]) . '</div>',
      ],
      '#cache' => [
        'contexts' => ['user'],
        'tags' => $user->getCacheTags(),
      ],
    ];
  }

}
