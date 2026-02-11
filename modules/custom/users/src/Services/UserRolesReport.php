<?php

namespace Drupal\users\Services;

use Drupal\Core\Database\Connection;


final class UserRolesReport {

  public function __construct(
    private readonly Connection $database,
  ) {}

  /**
   * Retorna usuarios con sus roles.
   *
   * @return array<int, array{uid:int, name:string, mail:string, roles:string[]}>
   */
  public function getUsersWithRoles(): array {
    // users_field_data: datos base del usuario.
    // user__roles: roles asignados (una fila por rol).
    $query = $this->database->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name', 'mail']);

    $query->leftJoin('user__roles', 'ur', 'ur.entity_id = u.uid');
    $query->addField('ur', 'roles_target_id', 'role_id');

    // Opcional: excluir bloqueados o incluir solo activos.
    // $query->condition('u.status', 1);

    // Evitar el usuario anónimo (uid 0) si quieres.
    $query->condition('u.uid', 0, '>');

    $query->orderBy('u.uid', 'ASC');

    $result = $query->execute();

    $users = [];
    foreach ($result as $row) {
      $uid = (int) $row->uid;
      if (!isset($users[$uid])) {
        $users[$uid] = [
          'uid' => $uid,
          'name' => (string) $row->name,
          'mail' => (string) $row->mail,
          'roles' => [],
        ];
      }

      if (!empty($row->role_id)) {
        $users[$uid]['roles'][] = (string) $row->role_id;
      }
    }


    foreach ($users as &$u) {
      $u['roles'] = array_values(array_unique($u['roles']));
    }

    return array_values($users);
  }

}