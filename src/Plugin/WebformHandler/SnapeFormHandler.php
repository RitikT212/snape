<?php
namespace Drupal\snape\Plugin\WebformHandler;

use \Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;


/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "snape_reviews",
 *   label = @Translation("Mailchimp Handler"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Mailchimp handler with tag."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class SnapeFormHandler extends WebformHandlerBase {
  
	 public function defaultConfiguration() {
    return [];
  }

  const MAILCHIMP_API_KEY = 'Enter_your_api_key'; 
  const LIST_ID = 'Enter_your_listID'; 
  const SERVER_LOCATION = 'your_server_location'; 

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $values = $webform_submission->getData();
    $email = strtolower($values['email_address']);
    $first_name = '';
    $last_name = '';

    // The data to send to the API
    $postData = array(
      "email_address" => "$email",
      "status" => "subscribed",
      'tags'  => array('site.com Subscription'),
      "merge_fields" => array(
        "FNAME" => "$first_name",
        "LNAME" => "$last_name"
      )
    );

    // Setup cURL
    $ch = curl_init('https://'.self::SERVER_LOCATION.'');
    curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
        'Authorization: apikey '.self::MAILCHIMP_API_KEY,
        'Content-Type: application/json'
      ),
      CURLOPT_POSTFIELDS => json_encode($postData)
    ));

    if(!$readable_response) {
      \Drupal::messenger()->addError('Something went wrong. Please contact your webmaster.');
    }
    if($readable_response->status == 403) {
      \Drupal::messenger()->addError('Something went wrong. Please contact your webmaster.');
    }
    if($readable_response->status == 'subscribed') {
      \Drupal::messenger()->addStatus('You are now successfully subscribed.');
    }
    if($readable_response->status == 400) {
      if($readable_response->title == 'Member Exists') {
        \Drupal::messenger()->addWarning('You are already subscribed to this mailing list.');
      }
    }
    return true;
  }
}
