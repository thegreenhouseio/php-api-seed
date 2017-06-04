<?php

namespace services;

use PHPMailer as PHPMailer;

/**
 *
 * @api tgh-api
 * @package services
 * @class ContactService
 *
 * @since 0.1.0
 *
 * @copyright 2017
 *
 */
class ContactService {
  private $mail;

  function __construct($config){
    $this->mail = new PHPMailer;

    //$this->mail->SMTPDebug = 3; // Enable verbose debug output

    $this->mail->isSMTP();
    $this->mail->Host = $config['host'];
    $this->mail->SMTPAuth = true;
    $this->mail->Username = $config['username'];
    $this->mail->Password = $config['password'];
    $this->mail->SMTPSecure = 'ssl';
    $this->mail->Port = $config['port'];
  }

  public function sendEmail($to = "", $from = "", $subject = "", $body = "", $isHtmlMail = false){
    $status = 500;
    $message = "";
    $error = null;

    if($to === "" || !filter_var($to, FILTER_VALIDATE_EMAIL)){
      $message = "Invalid to email address";
    }

    if($message === "" && ($from === "" || !filter_var($from, FILTER_VALIDATE_EMAIL))){
      $message = "Invalid from email address";
    }

    if($message === "" && $subject === ""){
      $message = "No subject";
    }

    if($message === "" && $body === ""){
      $message = "No message";
    }

    $error = $message === "" ? false : true;

    if(!$error){
      $body = wordwrap($body, 70, "\r\n");

      $this->mail->setFrom($from, $from);
      $this->mail->addAddress($to, $to);
      $this->mail->isHTML($isHtmlMail);

      $this->mail->Subject = $subject;
      $this->mail->Body = $body;

      if($this->mail->send()) {
        $message = "Message has been sent";
        $status = 200;
      } else {
        $message = 'Message could not be sent.';
        //echo 'Mailer Error: ' . $this->mail->ErrorInfo;
      }
    }else{
      $status = 400;
    }

    return array(
      "status" => $status,
      "message" => $message
    );

  }
}