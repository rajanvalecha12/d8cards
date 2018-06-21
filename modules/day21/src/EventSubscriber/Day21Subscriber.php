<?php
/**
 * Created by PhpStorm.
 * User: rajan.valecha
 * Date: 19/06/18
 * Time: 1:50 PM
 */
namespace Drupal\day21\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Day21Subscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('addAccessAllowOriginHeaders');
    return $events;
  }
  public function addAccessAllowOriginHeaders(FilterResponseEvent $event) {
    if (\Drupal::currentUser()->isAnonymous()) {
      $response = $event->getResponse();
      $response->headers->set('Access-Control-Allow-Origin', '*');
    }
  }
}
