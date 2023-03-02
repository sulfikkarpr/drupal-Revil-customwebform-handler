<?php

namespace Drupal\custom_webform_handler\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "custom_webform_handler",
 *   label = @Translation("Custom webform handler"),
 *   category = @Translation("for knowing the link is clicked or not"),
 *   description = @Translation("for knowing the link is clicked or not"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */


 class CustomWebformHandler extends WebformHandlerBase implements ContainerFactoryPluginInterface {

    protected $mailManager;
    protected $loggerFactory;
    protected $languageManager;

    public function __construct(array $configuration, $plugin_id, $plugin_definition,MailManagerInterface $mailManager,LoggerChannelFactoryInterface $logger_factory,LanguageManagerInterface $language_manager ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
      $this->mailManager = $mailManager;
      $this->loggerFactory = $logger_factory->get('custom_webform_handler');
      $this->languageManager = $language_manager;
    }


    public static function create(ContainerInterface $container, array $configuration, $plugin_id , $plugin_definition ) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('plugin.manager.mail'),
        $container->get('logger.factory'),
        $container->get('language_manager')
      );
    }


    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission){


      // for getting webmission id

      $webform_submission->setSticky(!$webform_submission->isSticky())->save();
      $sid = $webform_submission->id();
      $this->loggerFactory->debug('sid: ' . $sid);


      // generate url...........

      $url = Url::fromRoute('custom_webform_handler.handle_click', ['sid' => $sid], ['absolute' => TRUE]);
      $link = $url->toString();



      $module = 'custom_webform_handler';
      $key = 'all'; //module switch case


      $message = 'Thank you for submitting the form. We will get back to you as soon as possible.';//message

      $to = $webform_submission->getElementData('email');//get email from webform


      //parameters setting..

      $params['message'] = $message;
      $params['title'] = 'Webform Submission';
      $params['subject'] = 'webform email';
      $params['body'] = "Thank you for submitting the form. We will get back to you as soon as possible.
      please click the link for confirmation :$link";


      //set boolean value to send mail = true


      $send = true;


      //language


      $language = $this->languageManager->getCurrentLanguage()->getId();
      try {


        $result = $this->mailManager->mail($module, $key, $to, $language, $params, NULL, $send);//message sending format

        $this->loggerFactory->info('result  %email - ' . $to . '-'. print_r($result, TRUE));
        $this->loggerFactory->notice('Email sent to %email' , array('%email' => $to));


        // mail sent successfully


      } catch (Exception $e) {


        // handle the exception here


        $this->loggerFactory->error('Email not sent to %email - ' . $to . '-'. print_r($e->getMessage(), TRUE));


      }


    }


    /**
     * {@inheritdoc}
     */


     public function defaultConfiguration() {
      return parent::defaultConfiguration();
    }


    /**
     * {@inheritdoc}
     */

     public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
      $form = parent::buildConfigurationForm($form, $form_state);

      return $form;
    }

  }