<?php

namespace Drupal\users\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\users\Services\UsersRolesEntityReport;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class UserRolesEntityController extends ControllerBase {

  public function __construct(
    private readonly UsersRolesEntityReport $report,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('users.entity_roles_report'),
    );
  }

  
  public function list(): array {

  $data = $this->report->getUsersWithRoles();
  return [
    '#theme' => 'users_roles_report',
    '#users' => $data,
    '#cache' => [
      'max-age' => 0,
    ],
  ];
}

}