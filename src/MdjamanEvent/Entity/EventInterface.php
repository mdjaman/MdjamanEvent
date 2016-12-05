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

namespace MdjamanEvent\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Class Event
 * @package Event\Entity
 */
interface EventInterface
{
    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * @param string $name
     * @return Event
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * Set alias
     *
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias);

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias();

    /**
     * Set hits
     *
     * @param integer $hits
     * @return $this
     */
    public function setHits($hits = 0);

    /**
     * Get hits
     *
     * @return integer
     */
    public function getHits();

    /**
     * Set active
     *
     * @param boolean|int $active
     * @return $this
     */
    public function setActive($active = 1);

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive();

    /**
     * Set feature
     *
     * @param boolean|int $feature
     * @return $this
     */
    public function setFeature($feature = 0);

    /**
     * Get feature
     *
     * @return boolean
     */
    public function getFeature();

    /**
     * @return string
     */
    public function getDetails();

    /**
     * @param string $details
     * @return Event
     */
    public function setDetails($details);

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @param string $address
     * @return Event
     */
    public function setAddress($address);

    /**
     * @return \DateTime
     */
    public function getStartDate();

    /**
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate(\DateTime $startDate);

    /**
     * @return \DateTime
     */
    public function getEndDate();

    /**
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate(\DateTime $endDate = null);

    /**
     * Get cmtopen
     *
     * @return boolean
     */
    public function getCmtopen();

    /**
     * Set cmtopen
     *
     * @param boolean|int $cmtopen
     * @return $this
     */
    public function setCmtopen($cmtopen = 1);

    /**
     * @return TypeInterface
     */
    public function getType();

    /**
     * @param TypeInterface $type
     * @return Event
     */
    public function setType(TypeInterface $type);

    /**
     * Set img
     *
     * @param string $img
     * @return $this
     */
    public function setImg($img);

    /**
     * Get img
     *
     * @return string
     */
    public function getImg();

    /**
     * Add tag
     *
     * @param Collection $tags
     * @return $this
     */
    public function addTags(Collection $tags);

    /**
     * @param Collection $tags
     * @return $this
     */
    public function removeTags(Collection $tags);

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags();

}