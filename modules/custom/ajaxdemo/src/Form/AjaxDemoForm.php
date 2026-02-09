<?php

namespace Drupal\ajaxdemo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AjaxDemoForm extends FormBase {

  public function getFormId() {
    return 'ajaxdemo_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

  $form['name'] = [
    '#type' => 'select',
    '#title' => $this->t('Nombres'),
    '#options' => [
      '1' => $this->t('David'),
      '2' => $this->t('Esteban'),
      '3' => $this->t('Valentina'),
      '4' => $this->t('Santiago'),
    ],
    '#ajax' => [
      'callback' => '::myAjaxCallback',
      'event' => 'change',
      'wrapper' => 'ajaxdemo-output',
    ],
  ];

  $form['apellidos'] = [
    '#type' => 'select',
    '#title' => $this->t('Apellidos'),
    '#options' => [
      '1' => $this->t('Perez'),
      '2' => $this->t('Rodríguez'),
      '3' => $this->t('Sánchez'),
      '4' => $this->t('Pérez'),
    ],
    '#ajax' => [
      'callback' => '::myAjaxCallback',
      'event' => 'change',
      'wrapper' => 'ajaxdemo-output',
    ],
  ];

  // Claves seleccionadas (strings).
  $name_key = $form_state->getValue('name') ?? '1';
  $apellido_key = $form_state->getValue('apellidos') ?? '1';

  // Labels a partir de las opciones.
  $name_label = $form['name']['#options'][$name_key] ?? $this->t('Unknown');
  $apellido_label = $form['apellidos']['#options'][$apellido_key] ?? $this->t('Unknown');

  // Un solo wrapper que contiene ambos outputs.
  $form['output_wrapper'] = [
    '#type' => 'container',
    '#attributes' => ['id' => 'ajaxdemo-output'],
  ];

  $form['output_wrapper']['name_output'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Nombre seleccionado'),
    '#disabled' => TRUE,
    '#value' => $name_label,
  ];

  $form['output_wrapper']['apellido_output'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Apellido seleccionado'),
    '#disabled' => TRUE,
    '#value' => $apellido_label,
  ];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Submit'),
  ];

  return $form;
}

public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
  return $form['output_wrapper'];
}


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Submitted value: @v', [
      '@v' => $form_state->getValue('name'),
    ]));
  }

}