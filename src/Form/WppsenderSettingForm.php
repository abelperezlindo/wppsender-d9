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

    if($config->get('host') && $config->get('port')){
      $form['test_connection'] = [
        '#type' => 'submit',
        '#value' => 'Test connection',
        '#name' => 'test_connection',
        '#submit' => ['::testConection'],
      ];
      $form['new_session'] = [
        '#type' => 'submit',
        '#value' => 'New Session',
        '#name' => 'new_session',
        '#submit' => ['::getQr'],
      ];      
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $this->config('wppsender.settings')
      ->set('host', $form_state->getValue('host'))
      ->set('port', $form_state->getValue('port'))
      ->save();
    parent::submitForm($form, $form_state);
  }
  public function testConection(){
    $status = $this->wpp->getApiStatus();
    if($status){
      $this->messenger->addStatus('Connectado');
    } else {
      $this->messenger->addStatus('No connectado');
    }
  }

  public function getQr(){
    $qr = $this->wpp->addNewSession();
  }
}
