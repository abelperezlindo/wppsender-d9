<?php

namespace Drupal\wppsender\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\wppsender\Service\WhatsappSender;

/**
 * Defines a form that configures forms module settings.
 */
class WppsenderSettingForm extends ConfigFormBase  implements FormInterface{
  /**
   * The WhatsApp Notification Service
   *
   * @var Drupal\wppsender\Service\WhatsappSender;
  */
  protected $wpp; 
  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
  */
  public function __construct(WhatsappSender $wpp, MessengerInterface $messenger){
    $this->wpp = $wpp;
    $this->messenger = $messenger;
  }
  /**
   * {@inheritDoc}
  */
  public static function create(ContainerInterface $container){
    return new static(
      // Load the service required to construct this class.
      $container->get('wppsender.sender'),
      $container->get('messenger')
    );
  }
  
  public function getFormId() {
    return 'wppsender_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wppsender.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wppsender.settings');
    $connected = $config->get('connected');
    $form['container'] = [
      '#type' => 'container'
    ];
    $form['container']['host'] = [
      '#type' => 'textfield',
      '#required' => true,
      '#title' => $this->t('Host'),
      '#default_value' => $config->get('host'),
    ];
    $form['container']['port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Port'),
      '#required' => true,
      '#default_value' => $config->get('port'),
    ];

    if($config->get('connected')){
      $cron = $this->getCronStatus();

      if($cron){
        $form['stop cron'] = [
          '$preffix' => '<label>Cron está activo</label>',
          '#type' => 'submit',
          '#value' => 'Stop cron',
          '#name' => 'Stop cron',
          '#submit' => ['::stopCron'],
        ]; 
      } else {
        $form['start cron'] = [
          '$preffix' => '<label>Cron está inactivo</label>',
          '#type' => 'submit',
          '#value' => 'Start cron',
          '#name' => 'start cron',
          '#submit' => ['::startCron'],
        ]; 
      }
     
      $form['get new session'] = [
        '#type' => 'submit',
        '#value' => 'Get New Session',
        '#name' => 'get_session',
        '#submit' => ['::getQr'],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $host = $form_state->getValue('host');
    $port = $form_state->getValue('port');

    $this->config('wppsender.settings')
      ->set('host', $host)
      ->set('port', $port)
      ->set('connected', false)
      ->save();

    
    $status = $this->wpp->getApiStatus();
    if($status){
      $this->messenger->addStatus('Connectado');
      $this->config('wppsender.settings')->set('connected', true)->save();
    } else {
      $this->messenger->addStatus('No connectado');
    }
    parent::submitForm($form, $form_state);

  }

  public function getQr(){
    $data = $this->wpp->addNewSession();
    if($data !== false) {
      $this->messenger->addStatus($data);
    }
  }
  public function getCronStatus(){
    $status = $this->wpp->getCronStatus();
    return $status;
  }
  public function startCron(){
    $status = $this->wpp->startCron();
    return $status;
  }
  public function stopCron(){
    $status = $this->wpp->stopCron();
    return $status;
  }
}
