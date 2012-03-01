<?php

/*
 * Classe per la gestione della signed_request nelle applicazioni di FB
 * 
 * Esempio di utilizzo:
 * 
 * require ('FbSignedRequest.class.php');
 * 
 * $sr = new FBSignedRequest($_REQUEST, <SECRET KEY APPLICAZIONE>);
 * 
 * per recuperare i dati contenuti nella signed_request
 * $data = $sr->getData(); 
 * 
 * per sapere se l'utente è fan della pagina
 * $sr->isPageFan()
 * 
 */
define("SIGNED_REQUEST", 'signed_request');

class FBSignedRequest {
  
  private $secret = NULL;
  private $signed_req = NULL;
  private $data = NULL;
  
  public function __construct ($request= false, $secret = false) {
    if ($request && $secret) {
      $this->secret = $secret;
      if (key_exists(SIGNED_REQUEST, $request)) {
        $this->signed_req = $request[SIGNED_REQUEST];
        $this->data = $this->parse_signed_request();
      }//if
    } //if
  }//__construct

  private function parse_signed_request() {
    list($encoded_sig, $payload) = explode('.', $this->signed_req, 2); 
  
    // decode the data
    $sig = $this->base64_url_decode($encoded_sig);
    $data = json_decode($this->base64_url_decode($payload), true);
  
    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
      error_log('Unknown algorithm. Expected HMAC-SHA256');
      return null;
    }
  
    // check sig
    $expected_sig = hash_hmac('sha256', $payload, $this->secret, $raw = true);
    if ($sig !== $expected_sig) {
      error_log('Bad Signed JSON signature!');
      return null;
    }//parse_signed_request
  
    return $data;
  }//parse_signed_request

  private function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
  }//base64_url_decode
  
  public function getData() {
    return $this->data;
  }//getData
  
  public function isPageFan () {
    return $this->signed_req?$this->data['page']['liked'] : false;
  }//isPageFan
  
}//FBSignedRequest 
