<?php

namespace Drupal\users\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\RoleInterface;
use Drupal\user\RoleStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;

final class UsersRolesEntityReport {

  private UserStorageInterface $userStorage;
  private RoleStorageInterface $roleStorage;

  public function __construct(EntityTypeManagerInterface $entityTypeManager)
  {
    /** @var \Drupal\user\UserStorageInterface $user_storage */
    $user_storage = $entityTypeManager->getStorage('user');
    $this->userStorage = $user_storage;

    /** @var \Drupal\user\RoleStorageInterface $role_storage */
    $role_storage = $entityTypeManager->getStorage('user_role');
    $this->roleStorage = $role_storage;
  }

  /**
   * Retorna usuarios con roles (API de Drupal, sin SQL).
   *
   * @param bool $only_active
   *   TRUE para traer solo activos.
   * @param bool $include_role_labels
   *   TRUE para traer labels legibles de los roles.
   *
   * @return array<int, array{uid:int, name:string, mail:string, roles:string[], role_labels?:array<string,string>}>
   */
  public function getUsersWithRoles(bool $only_active = TRUE, bool $include_role_labels = TRUE): array {
    $query = $this->userStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', 0, '>')
      ->sort('uid', 'ASC');

    if ($only_active) {
      $query->condition('status', 1);
    }

    $uids = $query->execute();
    if (!$uids) {
      return [];
    }

    $accounts = $this->userStorage->loadMultiple($uids);

    // Opcional: precargar labels de roles (para no cargar uno por uno).
    $role_labels_map = [];
    if ($include_role_labels) {
      $roles = $this->roleStorage->loadMultiple();
      foreach ($roles as $rid => $role) {
        if ($role instanceof RoleInterface) {
          $role_labels_map[$rid] = $role->label();
        }
      }
    }

    $out = [];
    foreach ($accounts as $account) {
      if (!$account instanceof UserInterface) {
        continue;
      }

      $roles = array_values(array_diff($account->getRoles(), ['authenticated']));

      $row = [
        'uid' => (int) $account->id(),
        'name' => (string) $account->getAccountName(),
        'mail' => (string) $account->getEmail(),
        'roles' => array_values($roles),
      ];

      if ($include_role_labels) {
        $labels = [];
        foreach ($roles as $rid) {
          $labels[$rid] = $role_labels_map[$rid] ?? $rid;
        }
        $row['role_labels'] = $labels;
      }

      $out[] = $row;
    }

    return $out;
  }

}