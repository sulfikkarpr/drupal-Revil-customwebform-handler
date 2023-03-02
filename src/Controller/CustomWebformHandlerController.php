<?php

namespace Drupal\custom_webform_handler\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Returns responses for Custom Webform Handler routes.
 */


class CustomWebformHandlerController extends ControllerBase {


  /**
   * Builds the response.
   */


  public function handleClick($sid) {


   /** @var \Drupal\webform\Entity\WebformSubmissionInterface $webform_submission */


   $webform_submission = WebformSubmission::load($sid);
   $data = $webform_submission->getData();
   $data['value']= 'true';
   $webform_submission->setData($data);
   $webform_submission->save();
   return new RedirectResponse('/home');


  }

}
