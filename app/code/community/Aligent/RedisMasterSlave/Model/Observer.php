<?php
class Aligent_RedisMasterSlave_Model_Observer extends Mage_Core_Model_Abstract {

    const XML_PATH_SLAVES = 'aligent_redismasterslave/redismasterslave/slaves';
    protected $slaves = null;

    public function cacheClean($observer){
        $tags = $observer->getTags();
        if(!empty($tags) && !is_array($tags)) {
            $tags = array($tags);
        }
        $this->flushTags($tags);
        return $this;
    }

    public function cacheRefreshType($observer){
        $type = $observer->getType();
        $this->flushTags(array($type));

        return $this;
    }

    protected function prefixTags(&$tags) {
        /*
         * Get cache prefix.  Assuming that this is the same on all master and slave redis databases,
         * otherwise the cache flush won't be effective
         */
        $cacheIdPrefix = Mage::app()->getCacheInstance()->getFrontend()->getOption('cache_id_prefix');

        foreach ($tags as $key=>$tag) {
            $tags[$key] = $cacheIdPrefix . $tag;
        }
        return $tags;
    }


    protected function flushTags(array $tags) {
        $tags = $this->prefixTags($tags);
        foreach ($this->getRedisSlaves() as $slave) {
            $success = $slave->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            if(!$success) {
                Mage::log("Unable to flush cache at host " . $slave['server'], Zend_Log::ERR);
            }
        }
    }

    protected function getRedisSlaves() {
        if($this->slaves == null) {
            $this->slaves = array();
            $slaveConfigs = $this->getSlaveConfigs();
            if ($slaveConfigs) {
                foreach ($slaveConfigs as $slaveConfig) {
                    try {
                        $this->slaves[] = new Cm_Cache_Backend_Redis($slaveConfig);
                    } catch (Exception $e) {
                        Mage::log("Unable to connect to slave redis host " . $slaveConfig['server'], Zend_Log::ERR);
                    }

                }
            }
        }
        return $this->slaves;
    }

    protected function getSlaveConfigs() {
        return unserialize(Mage::getConfig()->getNode(self::XML_PATH_SLAVES, 'default'));
    }

}
