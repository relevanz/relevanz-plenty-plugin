<?php
namespace Relevanz\Checks;

use Plenty\Plugin\Build\CheckProcess;
use Plenty\Plugin\ConfigRepository;

/**
 * Class Credentials
 * @package Relevanz\Checks
 */
class Credentials extends CheckProcess
{
    public function check()
    {
        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);

        $apiKey = $configRepository->get('Relevanz.apiKey');

        if (empty($apiKey)) {
            $this->addError('Relevanz: API Key is missing.');
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://backend.releva.nz/v1/campaigns/get?apikey=".$apiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if(!array_key_exists('user_id', $output)){
                $this->addError('Relevanz: '.$output['message']);
            }
        }
    }
}