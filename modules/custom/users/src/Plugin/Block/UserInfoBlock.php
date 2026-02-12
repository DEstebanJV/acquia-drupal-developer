<?php

namespace Drupal\users\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * @Block(
 *   id = "users_user_info_block",
 *   admin_label = @Translation("User info (logged-in)")
 * )
 */
class UserInfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly AccountProxyInterface $currentUser,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
  }

  public function build(): array {
    // Si es anónimo, muestra algo simple.
    if ($this->currentUser->isAnonymous()) {
      return [
        '#markup' => $this->t('No has iniciado sesión.'),
        '#cache' => [
          'contexts' => ['user'], // también cambia entre anónimo/autenticado
        ],
      ];
    }

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->entityTypeManager
      ->getStorage('user')
      ->load($this->currentUser->id());

    $name = $user->getDisplayName();
    $roles = $user->getRoles();
    $roles_label = implode(', ', $roles);

    return [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Nombre: @name', ['@name' => $name]),
        $this->t('Roles: @roles', ['@roles' => $roles_label]),
      ],
      '#cache' => [
        // ✅ CLAVE: varía por usuario actual (incluye nombre/roles)
        'contexts' => ['user'],
        // ✅ Recomendado: se invalida si cambia este usuario
        'tags' => $user->getCacheTags(),
      ],
    ];
  }

}