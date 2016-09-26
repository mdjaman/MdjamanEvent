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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use MdjamanCommon\Entity\BaseEntity;

/**
 * Tag
 *
 * @ORM\Table(name="event_tag")
 * @ORM\Entity(repositoryClass="MdjamanEvent\Repository\TagRepository")
 */
class Tag extends BaseEntity implements TagInterface
{
    /**
     * @var string $id
     *
     * @ORM\Column(name="id", type="string", length=36, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @JMS\Groups({"list", "details"})
     */
    protected $id;

    /**
     * @var string $name
     * 
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     * @JMS\Groups({"list", "details"})
     */
    protected $name;

    /**
     * @var string $alias
     * 
     * @ORM\Column(name="alias", type="string", length=50, nullable=true, unique=true)
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @JMS\Groups({"list", "details"})
     */
    protected $alias;

    /**
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="tags", cascade={"all"})
     * @JMS\MaxDepth(2)
     * @JMS\Groups({"details"})
     */
    protected $events;


    public function __construct()
    {
        parent::__construct();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
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
     * Add event
     *
     * @param EventInterface $event
     * @return $this
     */
    public function addEvent(EventInterface $event)
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }
        return $this;
    }

    /**
     * @param EventInterface $event
     */
    public function removeEvent(EventInterface $event)
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
        }
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

}
