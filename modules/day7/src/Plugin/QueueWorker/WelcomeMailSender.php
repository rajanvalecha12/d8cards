<?php
/**
 * @file
 * Contains Drupal\d8cards_day07\Plugin\QueueWorker\WelcomeMailSenderBase.php
 */

namespace Drupal\day7\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A Welcome email sender that sends welcome email to the newly registered user.
 *
 * @QueueWorker(
 *   id = "cron_welcome_mail_sender",
 *   title = @Translation("Cron Welcome Mail Sender"),
 *   cron = {"time" = 10}
 * )
 */
class WelcomeMailSender extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var MailManagerInterface
   */
  protected $mailManager;


  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Logger
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Creates a new WelcomeMailSenderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param MailManagerInterface $mailManager
   *   Mail manager service.
   * @param EntityStorageInterface $userStorage
   *   User Storage
   * @param LoggerChannelInterface $logger
   *   Logger
   */
  public function __construct( MailManagerInterface $mailManager, EntityStorageInterface $userStorage, LoggerChannelInterface $logger) {
    $this->mailManager = $mailManager;
    $this->userStorage = $userStorage;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $container->get('plugin.manager.mail'),
        $container->get('entity.manager')->getStorage('user'),
        $container->get('logger.factory')->get('welcome_mail_sender')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    $mailManager = $this->mailManager;
    $module = 'day7';
    $key = 'welcome_email';

    /** @var User $user */
    $user = $this->userStorage->load($data->nid);
    $to = $user->getEmail();
    $login = $user->getAccountName();
    $params['message'] = $this->t('Hi :login , this is your new registration!', [':login' => $login])->__toString();
    $params['subject'] = $this->t('Your new registration')->__toString();
    $langcode = $user->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== true) {
      $this->logger->warning('There was a problem sending an email to %email', ['%email' => $to]);
    }
    else {
      $this->logger->notice('Email to %email sent successfully', ['%email' => $to]);
    }
  }
}
