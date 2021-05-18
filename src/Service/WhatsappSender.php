<?php 

namespace Drupal\wppsender\Service;

//use Drupal\wppsender\Service\RequestException;
//\GuzzleHttp\Exception\RequestException
use GuzzleHttp\Exception\RequestException;
use Zend\Diactoros\Response\EmptyResponse;

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
        if(empty($this->host) || empty($this->port)){
            return false;
        }
        $url = 'http://' . $this->host . ':' . $this->port;
        $client = \Drupal::httpClient();
        try {
            $response = $client->get($url);
            $code = $response->getStatusCode();
            return ($code == 200);
        }
        catch (RequestException $e) {
            $mensaje = $e;
            return false;
        }
    }
    public function addNewSession(){
        $url = $this->getUrl() . '/session/qr';

        $response = \Drupal::httpClient()
        ->get($url, []);
        $code = $response->getStatusCode();
        if($code !== 200) return '';
        $json_string = (string) $response->getBody();
        $body = json_decode($json_string);
        return $body->qr;
    }
    /** @todo get cron status from api */
    public function getCronStatus(){
        $url = $this->getUrl() . '/cron';
        $response = \Drupal::httpClient()
        ->get($url, []);
        $code = $response->getStatusCode();
        if($code !== 200) return '';
        $json_string = (string) $response->getBody();
        $body = json_decode($json_string);
        return $body->cronStatus;
    }
    /** @todo get cron status from api */
    public function startCron(){

        $cronStatus = $this->getCronStatus();
        if($cronStatus) return true; // Ya esta start
        $url = $this->getUrl() . '/cron/start';
        $response = \Drupal::httpClient()
        ->get($url, []);
        $code = $response->getStatusCode();
        if($code !== 200) return '';
        $json_string = (string) $response->getBody();
        $body = json_decode($json_string);
        return $body->cronStatus;

    }
    public function stopCron(){
        $cronStatus = $this->getCronStatus();
        if(!$cronStatus) return true; // Ya esta start
        $url = $this->getUrl() . '/cron/start';
        $response = \Drupal::httpClient()
        ->get($url, []);
        $code = $response->getStatusCode();
        if($code !== 200) return '';
        $json_string = (string) $response->getBody();
        $body = json_decode($json_string);
        return $body->cronStatus;
    }
    protected function getUrl(){
        return 'http://' . $this->host . ':' . $this->port;
    }
}