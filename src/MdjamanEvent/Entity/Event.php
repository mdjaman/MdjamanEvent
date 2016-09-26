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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use MdjamanCommon\Entity\BaseEntity;
use MdjamanCommon\Traits\BlameableEntity;

/**
 * @ORM\Table(name="event_event")
 * @ORM\Entity(repositoryClass="MdjamanEvent\Repository\EventRepository")
 */
class Event extends BaseEntity implements EventInterface
{

    use BlameableEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=36, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @JMS\Groups({"list", "details"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @JMS\Groups({"list", "details"})
     */
    protected $name;

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=50, nullable=false, unique=true)
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @JMS\Groups({"list", "details"})
     */
    protected $alias;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=true, unique=false)
     * @JMS\Groups({"list", "details"})
     */
    protected $active;

    /**
     * @var string $img
     *
     * @ORM\Column(name="img", type="text", nullable=true)
     * @JMS\Groups({"list", "details"})
     */
    protected $img;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="feature", type="boolean", nullable=true, unique=false)
     * @JMS\Groups({"details"})
     */
    protected $feature = 0;

    /**
     * @var integer $hits
     *
     * @ORM\Column(name="hits", type="integer", nullable=true, unique=false)
     * @JMS\Groups({"details"})
     */
    protected $hits = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     * @JMS\Groups({"details"})
     */
    protected $details;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     * @JMS\Groups({"details"})
     */
    protected $address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     * @JMS\Groups({"list", "details"})
     */
    protected $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     * @JMS\Groups({"list", "details"})
     */
    protected $endDate;

    /**
     * @var boolean $cmtopen
     *
     * @ORM\Column(name="cmtopen", type="boolean", nullable=true)
     * @JMS\Groups({"list", "details"})
     */
    protected $cmtopen = 0;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="Type")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     * })
     * @JMS\MaxDepth(1)
     * @JMS\Groups({"list", "details"})
     */
    protected $type;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="events", cascade={"all"})
     * @ORM\JoinTable(name="event_event_tag",
     *   joinColumns={
     *      @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *      @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *   }
     * )
     * @JMS\MaxDepth(3)
     * @JMS\Groups({"details"})
     */
    protected $tags;


    public function __construct()
    {
        parent::__construct();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set active
     *
     * @param boolean|int $active
     * @return $this
     */
    public function setActive($active = 1)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set img
     *
     * @param string $img
     * @return $this
     */
    public function setImg($img)
    {
        $this->img = $img;
        return $this;
    }

    /**
     * Get img
     *
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return Event
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Event
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Set feature
     *
     * @param boolean|int $feature
     * @return $this
     */
    public function setFeature($feature = 0)
    {
        $this->feature = $feature;
        return $this;
    }

    /**
     * Get feature
     *
     * @return boolean
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Set hits
     *
     * @param integer $hits
     * @return $this
     */
    public function setHits($hits = 0)
    {
        $this->hits = $hits;
        return $this;
    }

    /**
     * Get hits
     *
     * @return integer
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Get cmtopen
     *
     * @return boolean
     */
    public function getCmtopen()
    {
        return $this->cmtopen;
    }

    /**
     * Set cmtopen
     *
     * @param boolean|int $cmtopen
     * @return $this
     */
    public function setCmtopen($cmtopen = 1)
    {
        $this->cmtopen = $cmtopen;
        return $this;
    }

    /**
     * @return TypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param TypeInterface $type
     * @return Event
     */
    public function setType(TypeInterface $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Add tag
     *
     * @param Collection $tags
     * @return $this
     */
    public function addTags(Collection $tags)
    {
        foreach ($tags as $tag) {
            if (!$this->tags->contains($tag)) {
                $this->tags->add($tag);
            }
        }

        return $this;
    }

    /**
     * @param Collection $tags
     * @return $this
     */
    public function removeTags(Collection $tags)
    {
        foreach ($tags as $tag) {
            if ($this->tags->contains($tag)) {
                $this->tags->removeElement($tag);
            }
        }
        return $this;
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

}