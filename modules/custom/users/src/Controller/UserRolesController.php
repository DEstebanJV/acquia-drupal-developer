<?php

namespace Drupal\users\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\users\Services\UserRolesReport;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class UserRolesController extends ControllerBase {

  public function __construct(
    private readonly UserRolesReport $report,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('users.report'),
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