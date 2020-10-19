<?php
namespace Relevanz\Controllers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;

/**
 * Class Settings
 * @package Relevanz\Controllers
 */
class Settings extends Controller
{
    /**
     * @param ConfigRepository $configRepository
     * @return mixed
     */
    public function index(ConfigRepository $configRepository){
        return $configRepository->get('Relevanz');
    }
}