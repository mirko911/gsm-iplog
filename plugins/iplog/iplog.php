<?php
/**
 * GSManager
 *
 * This is a mighty and platform independent software for administrating game servers of various kinds.
 * If you need help with installing or using this software, please visit our website at: www.gsmanager.de
 * If you have licensing enquiries e.g. related to commercial use, please contact us at: sales@gsmanager.de
 *
 * @copyright Greenfield Concept UG (haftungsbeschränkt)
 * @license GSManager EULA <https://www.gsmanager.de/eula.php>
 * @version 1.2.1
**/

namespace GSM\Plugins\IPlog;

use GSM\Daemon\Core\Utils;

/**
 * IPlog plugin
 *
 * logs the chat to the log folder
 *
 */
class IPlog extends Utils {

    /**
     * Handles the writing of logfiles
     *
     * @var \GSM\Daemon\Libraries\Logging\LogHandler
     */
    private $loghandler;

    /**
     * Inits the plugin
     *
     * This function initiates the plugin. This means that it register commands
     * default values, and events. It's important that every plugin has this function
     * Otherwise the plugin exists but can't be used
     */
    public function initPlugin() {
        parent::initPlugin();
        $this->config->setDefault('iplog', 'enabled', false);
        $this->config->setDefault('iplog', 'string', '<TIME> <PLAYER_NAME> (<PLAYER_GUID>) <PLAYER_IP>');
        $this->config->setDefault('iplog', 'logname', 'iplog');
    }

    public function enable() {
        parent::enable();
        $this->events->register('playerJoined', [$this, 'onPlayerJoined']);
        $this->loghandler = new \GSM\Daemon\Libraries\Logging\LogHandler("plugins/", $this->config->get('iplog', 'logname'));
        $this->loghandler->setEcho(false);
    }

    public function disable() {
        parent::disable();
        $this->events->unregister('playerJoined', [$this, 'onPlayerJoined']);
        unset($this->loghandler);
    }

    public function onPlayerJoined($guid) {
        $search = [
          '<TIME>',
          '<PLAYER_NAME>',
          '<PLAYER_GUID>',
          '<PLAYER_PID>',
          '<PLAYER_IP>',
          '<COUNTRY_CODE>',
          '<COUNTRY_NAME>',
          '<CITY_NAME>',
          '<CONTINENT_CODE>',
          '<CONTINENT_NAME>',
        ];

        $sld = $this->players[$guid]->getSimpleLocationData();

        $replace = [
          date('Y-m-d H:i:s'),
          $this->players[$guid]->getName(),
          $guid,
          $this->players[$guid]->getPID(),
          $this->players[$guid]->getIP(),
          $sld['countrycode'],
          $sld['countryname'],
          $sld['cityname'],
          $sld['continentcode'],
          $sld['continentname'],
        ];

        $logline = str_replace($search, $replace, $this->config->get('iplog', 'string'));
        $this->loghandler->write($logline, false);
    }
}
