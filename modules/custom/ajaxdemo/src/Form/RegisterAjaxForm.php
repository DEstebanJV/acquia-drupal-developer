<?php

namespace Drupal\ajaxdemo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class RegisterAjaxForm extends FormBase {

  public function getFormId() {
    return 'ajaxdemo_register_ajax_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::usernameAjaxCallback',
        'event' => 'change',
        'wrapper' => 'ajaxdemo-username-wrapper',
      ],
    ];

    // Wrapper que se reemplaza por AJAX.
    $form['username_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajaxdemo-username-wrapper'],
    ];

    $email = (string) ($form_state->getValue('mail') ?? '');
    $suggested = $this->suggestUsernameFromEmail($email);

    $form['username_wrapper']['username_output'] = [
      '#type' => 'item',
      '#title' => $this->t('Tu nombre de usuario será'),
      '#markup' => $suggested
        ? $this->t('<strong>@name</strong>', ['@name' => $suggested])
        : $this->t('<em>Escribe un correo para ver tu usuario.</em>'),
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Contraseña'),
      '#required' => TRUE,
    ];

    $form['pass_confirm'] = [
      '#type' => 'password',
      '#title' => $this->t('Confirmar contraseña'),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Crear cuenta'),
    ];

    return $form;
  }

  public function usernameAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['username_wrapper'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mail = trim((string) $form_state->getValue('mail'));
    $pass = (string) $form_state->getValue('pass');
    $pass_confirm = (string) $form_state->getValue('pass_confirm');

    if ($pass !== $pass_confirm) {
      $form_state->setErrorByName('pass_confirm', $this->t('Las contraseñas no coinciden.'));
    }

    // Nombre de usuario derivado del email.
    $username = $this->suggestUsernameFromEmail($mail);
    if (!$username) {
      $form_state->setErrorByName('mail', $this->t('El correo no es válido para generar un nombre de usuario.'));
      return;
    }

    // Validar email único.
    $existing_by_mail = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['mail' => $mail]);
    if (!empty($existing_by_mail)) {
      $form_state->setErrorByName('mail', $this->t('Ya existe un usuario con este correo.'));
    }

    // Validar username único.
    $existing_by_name = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['name' => $username]);
    if (!empty($existing_by_name)) {
      $form_state->setErrorByName('mail', $this->t('El usuario "@u" ya existe. Prueba con otro correo.', ['@u' => $username]));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mail = trim((string) $form_state->getValue('mail'));
    $pass = (string) $form_state->getValue('pass');
    $username = $this->suggestUsernameFromEmail($mail);

    $account = User::create();
    $account->setUsername($username);
    $account->setEmail($mail);
    $account->setPassword($pass);

    // Activo + rol authenticated (aunque Drupal lo asigna por defecto).
    $account->activate();
    $account->addRole('content_editor');

    $account->save();

    $this->messenger()->addStatus($this->t('Usuario creado: @u', ['@u' => $username]));
    $form_state->setRedirect('user.login');
  }
  /**
   * Toma lo que va antes de @ y lo normaliza para username.
   */
  private function suggestUsernameFromEmail(string $email): ?string {
    $email = trim($email);
    if ($email === '' || strpos($email, '@') === FALSE) {
      return NULL;
    }

    $local = explode('@', $email, 2)[0];

    // Normaliza: deja letras, números, punto, guion y guion bajo.
    $local = strtolower($local);
    $local = preg_replace('/[^a-z0-9._-]+/', '', $local) ?? '';

    // Evita vacío.
    return $local !== '' ? $local : NULL;
  }

}