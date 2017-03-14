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

namespace MdjamanEvent\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use MdjamanEvent\Entity\EventInterface;
use MdjamanEvent\Options\ModuleOptionsInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MdjamanCommon\Service\AbstractService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class EventService
 * @package Event\Service
 */
class EventService extends AbstractService implements EventServiceInterface
{

    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     * EventService constructor.
     * @param ServiceManager $serviceManager
     * @param ObjectManager $om
     * @param ModuleOptionsInterface $options
     */
    public function __construct(ServiceManager $serviceManager, ObjectManager $om, ModuleOptionsInterface $options)
    {
        $this->options = $options;
        
        $entityClass = $options->getEventEntityClass();
        parent::__construct(new $entityClass, $om);

        $this->setServiceManager($serviceManager);
    }

    /**
     * Persists Event
     * @param array|EventInterface $data
     * @return EventInterface
     */
    public function saveEvent($data)
    {
        return $this->save($data);
    }

    /**
     * Save event and process tags
     *
     * @param EventInterface $event
     * @param mixed|string|array $tags
     * @return EventInterface
     */
    public function saveEventWithTags(EventInterface $event, $tags)
    {
        if (!is_array($tags) ) {
            $tags = explode(',', $tags);
        }
        $tagCollection = new ArrayCollection();
        $tagEntityClass = $this->options->getTagEntityClass();
        foreach ($tags as $item) {
            if ($item === '') {
                continue;
            }
            $tag = new $tagEntityClass;
            $tag->setName($item);
            $tagCollection->add($tag);
        }

        $event->addTags($tagCollection);

        return $this->save($event, true);
    }

    /**
     * Filter
     * @param array $filters
     * @return mixed
     */
    public function filter(array $filters = null)
    {
        $filter = null;
        $value = null;
        $criteria = [];
        $limit = $this->options->getRecentListingLimit();
        $sort = 'created_at';
        $offset = null;

        if (is_array($filters)) {
            extract($filters, EXTR_OVERWRITE);
        }

        $sort = !isset($sort) ? 'created_at' : $sort;

        if (!isset($dir) || !in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $orderBy = [$sort => $dir];

        switch ($filter) {
            default:
                if (is_null($filter) || $filter == '') {
                    $criteria = [];
                } else {
                    $criteria = [$filter => $value];
                }

                $entity = $this->findBy($criteria, $orderBy, $limit, $offset);
                break;
        }

        return $entity;
    }

    /**
     * @param $filters
     * @return mixed
     */
    public function countEvents($filters)
    {
        $filter = null;
        $value = null;
        $criteria = [];

        if (is_array($filters)) {
            extract($filters, EXTR_OVERWRITE);
        }

        if (is_null($filter) || $filter == '') {
            $criteria = [];
        } else {
            $criteria = [$filter => $value];
        }

        if (isset($user) && $user !== '') {
            $criteria['user'] = $user;
        }

        return (int) $this->getRepository()->countResult($criteria);
    }

    /**
     * @param array $filters
     * @return Criteria
     */
    protected function buildCriteria(array $filters)
    {
        $entity = $this->hydrate($filters, $this->createEntity());

        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $valid = 0;
        foreach ($filters as $key => $value) {
            $method = 'get' . ucfirst($key);
            if ($key === 'startDate') {
                $valid = 1;
            }

            if ($valid !== 1 && $key !== 'endDate') {
                $criteria->andWhere($expr->eq($key, $entity->{$method}()));
            }
        }

        return $criteria;
    }

    /**
     * Increment event hits
     * @param EventInterface $event
     */
    public function incrementEventHits(EventInterface $event)
    {
        $hits = $event->getHits();
        $event->setHits(++$hits);

        $this->objectManager->flush();
    }

}