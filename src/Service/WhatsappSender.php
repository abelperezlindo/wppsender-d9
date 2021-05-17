<?php 

namespace Drupal\wppsender\Service;

//use Drupal\wppsender\Service\RequestException;
//\GuzzleHttp\Exception\RequestException
use GuzzleHttp\Exception\RequestException;

class WhatsappSender {
    protected $status;
    protected $port;
    protected $host;

    public function __construct(){
        // Using the config service get the configuration setted by the user
        $config = \Drupal::config('wppsender.settings');
        // Set the token value
        $this->host = $config->get('host');
        // Set the instanceId value
        $this->port = $config->get('port');
    }
 
    public function getApiStatus(){
        $url = 'http://' . $this->host . ':' . $this->port;
        $client = \Drupal::httpClient();
        try {
            $response = $client->get($url);
            $data = $response->getBody();
            $code = $response->getStatusCode();
            if($code == 200){
                $this->status = true;
                return true;
            }
        }
        catch (RequestException $e) {
            $mensaje = $e;
            return false;
        }
    }
    public function addNewSession(){
        $url = 'http://' . $this->host . ':' . $this->port . '/qr';

        $client = \Drupal::httpClient();
        //$client->setPort()
        try {
            $response = $client->get($url);
            $code = $response->getStatusCode();
            $json = $response->json();
            if($code == 200){
                $this->status = true;
                return true;
            }
        }
        catch (RequestException $e) {
            $mensaje = $e;
            return false;
        }
    }
}