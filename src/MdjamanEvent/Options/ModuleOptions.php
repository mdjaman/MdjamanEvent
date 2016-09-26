<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Marcel Djaman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MdjamanEvent\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Description of ModuleOptions
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class ModuleOptions extends AbstractOptions implements ModuleOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * @var string
     */
    protected $eventEntityClass = 'MdjamanEvent\Entity\Event';

    /**
     * @var string
     */
    protected $typeEntityClass = 'MdjamanEvent\Entity\Type';

    /**
     * @var string
     */
    protected $tagEntityClass = 'MdjamanEvent\Entity\Tag';

    /**
     * @var int
     */
    protected $adminListingLimit = 20;
    
    /**
     * @var int
     */
    protected $recentListingLimit = 20;


    /**
     * Getter for adminListingLimit
     *
     * @return int
     */
    public function getAdminListingLimit()
    {
        return $this->adminListingLimit;
    }
    
    /**
     * Setter for adminListingLimit
     *
     * @param mixed $adminListingLimit Value to set
     * @return self
     */
    public function setAdminListingLimit($adminListingLimit)
    {
        $this->adminListingLimit = $adminListingLimit;
        return $this;
    }
    
    /**
     * Getter for recentListingLimit
     *
     * @return int
     */
    public function getRecentListingLimit()
    {
        return $this->recentListingLimit;
    }
    
    /**
     * Setter for recentListingLimit
     *
     * @param mixed $recentListingLimit Value to set
     * @return self
     */
    public function setRecentListingLimit($recentListingLimit)
    {
        $this->recentListingLimit = $recentListingLimit;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventEntityClass()
    {
        return $this->eventEntityClass;
    }

    /**
     * @param string $eventEntityClass
     * @return ModuleOptions
     */
    public function setEventEntityClass($eventEntityClass)
    {
        $this->eventEntityClass = $eventEntityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeEntityClass()
    {
        return $this->typeEntityClass;
    }

    /**
     * @param string $typeEntityClass
     * @return ModuleOptions
     */
    public function setTypeEntityClass($typeEntityClass)
    {
        $this->typeEntityClass = $typeEntityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getTagEntityClass()
    {
        return $this->tagEntityClass;
    }

    /**
     * @param string $tagEntityClass
     * @return ModuleOptions
     */
    public function setTagEntityClass($tagEntityClass)
    {
        $this->tagEntityClass = $tagEntityClass;
        return $this;
    }

}
