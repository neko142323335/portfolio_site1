<?php
namespace App\Controllers;

/**
 * Контролер контактів
 */
class ContactController extends BaseController
{
  public function index(): void
  {
    $sent = $this->getQuery('sent', 0);
    $error = '';
    $formName = '';
    $formEmail = '';
    $formMessage = '';

    if ($this->isPost()) {
      $formName = $this->getPost('name', '');
      $formEmail = $this->getPost('email', '');
      $formMessage = $this->getPost('message', '');
      $error = $this->handleContact();
      if (!$error) {
        $this->redirect('contact?sent=1');
      }
    }

    $this->render('contact.html.twig', [
      'sent' => $sent,
      'error' => $error,
      'form_name' => $formName,
      'form_email' => $formEmail,
      'form_message' => $formMessage,
      'logged_out' => 0,
    ]);
  }

  private function handleContact(): string
  {
    require_once __DIR__ . '/../../includes/helpers.php';
    
    $name = sanitize_input($this->getPost('name', ''));
    $email = sanitize_input($this->getPost('email', ''));
    $message = sanitize_input($this->getPost('message', ''));

    if (!$name || !$email || !$message) {
      return ERROR_MESSAGES['required_fields'];
    }

    if (!validate_email($email)) {
      return ERROR_MESSAGES['invalid_email'];
    }

    // Тут можна додати логіку відправки email або збереження в БД
    log_error('Contact form submission', [
      'name' => $name,
      'email' => $email,
      'message' => substr($message, 0, 100),
    ]);

    return '';
  }
}
