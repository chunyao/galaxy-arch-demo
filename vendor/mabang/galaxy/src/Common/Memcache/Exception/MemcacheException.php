<?php

namespace Mabang\Galaxy\Common\Memcache\Exception;

use Mabang\Galaxy\Common\Memcache\Package;

/**
 * Class MemcacheException
 * @package Galaxy\Common\Memcache\Exception
 */
class MemcacheException extends \Exception
{
    private $package;

    /**
     * Package Getter
     * @return Package
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * Package Setter
     * @param Package $package
     * @return MemcacheException
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
        return $this;
    }
}