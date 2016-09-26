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

namespace MdjamanEvent\Controller;

use MdjamanEvent\Exception;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEvent\Service\EventServiceInterface;
use MdjamanEvent\Service\TagServiceInterface;
use MdjamanEvent\Service\TypeServiceInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class EventController
 * @package Event\Controller
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class EventController extends AbstractActionController
{

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var TypeServiceInterface
     */
    protected $typeService;

    /**
     * @var TagServiceInterface
     */
    protected $tagService;

    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * Fetch list of events
     *
     * @return JsonModel|ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        $page = $this->params()->fromQuery('page', 1);
        $limit = $this->params()->fromQuery('limit', $this->getOptions()->getRecentListingLimit());
        $offsetParam = $this->params()->fromQuery('offset');
        $offset = isset($offsetParam) ? $offsetParam : ($page - 1) * $limit;

        $sort = $this->params()->fromQuery('sort', 'created_at');
        $dir = $this->params()->fromQuery('dir', 'desc');

        $inputFilter = $this->getInputFilter();
        $allowedFilters = $inputFilter->getInputs();
        $filters = [];
        foreach ($allowedFilters as $key => $value) {
            $filter = $this->params()->fromQuery($key, null);
            if ($filter !== null && $filter !== '') {
                $filters[$key] = $filter;
            }
        }

        if (!count($filters)) {
            $filteredValues = [];
        } else {
            $inputFilter->setData($filters)
                ->setValidationGroup(array_keys($filters));

            $filteredValues = $inputFilter->getValues();
        }

        $service = $this->getEventService();
        $events = $service->filters($filteredValues, [$sort => $dir], $limit, $offset);

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($events);
            $resultJson['total'] = $service->countMatchingRecords($filters);
            return new JsonModel($resultJson);
        }

        $viewModel = new ViewModel();
        $viewModel->setVariables([
            'event' => $events,
        ]);

        $agendaParam = $this->params()->fromQuery('agenda');
        if (null !== $agendaParam) {
            $viewModel->setTemplate('event/event/agenda');
        }
        return $viewModel;
    }

    /**
     * Fetch an event
     * @return mixed|JsonModel|ViewModel
     */
    public function viewAction()
    {
        $request = $this->getRequest();
        $service = $this->getEventService();
        $id = $this->params()->fromRoute('id', 0);
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        if ($id === 0) {
            return $this->forward()->dispatch('Event\Controller\Event', ['action' => 'index']);
        }

        try {
            $event = $service->find($id);

            if (!$event) {
                $message = sprintf(_('Evènement %s introuvable'), $id);
                throw new Exception\EventNotFoundException($message);
            }

        } catch (Exception\EventNotFoundException $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);
            $errMessage = $ex->getMessage();

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage(_('Evènement introuvable'));
            return $this->forward()->dispatch('Event\Controller\Event', ['action' => 'index']);
        }

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($event);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'event' => $event,
        ]);
    }

    /**
     * @return mixed|JsonModel|ViewModel
     * @throws Exception\TypeNotFoundException
     */
    public function typeAction()
    {
        $request = $this->getRequest();
        $viewQuery = $this->params()->fromQuery('viewHtml', 0);
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        $page    = $this->params()->fromQuery('page', 1);
        $limit   = $this->params()->fromQuery('limit', $this->getOptions()->getArchiveListingLimit());
        $offsetParam  = $this->params()->fromQuery('offset');
        $offset  = isset($offsetParam) ? $offsetParam : ($page - 1) * $limit;

        $sort = $this->params()->fromQuery('sort', 'created_at');
        $dir = $this->params()->fromQuery('dir', 'desc');

        $id = $this->params()->fromRoute('alias', '');

        if ($id === '') {
            return $this->forward()->dispatch('Event\Controller\Event', ['action' => 'index']);
        }

        $service = $this->getEventService();

        try {
            $typeService = $this->getTypeService();
            $type = $typeService->findOneBy(['alias' => $id]);

            if (!$type) {
                $message = sprint(_('Type d\'évènement %s introuvable'), $id);
                throw new Exception\TypeNotFoundException($message);
            }

            $entities = $service->getRepository()->findEventByType($type->getId(), true, null, $sort, $dir, $limit, $offset);
        } catch (\Exception $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);
            $errMessage = $ex->getMessage();

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage($errMessage);
            return $this->forward()->dispatch('Event\Controller\Event', ['action' => 'index']);
        }

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($entities, 'json', 'details');

            if ($viewQuery) {
                $htmlViewPart = new ViewModel();
                $htmlViewPart->setTerminal(true)
                    ->setTemplate('event/_partials/paginate/event')
                    ->setVariables(array(
                        'article' => $entities,
                    ));

                $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
                $htmlOutput = $viewRenderer->render($htmlViewPart);

                $resultJson['html'] = $htmlOutput;
            }
            //$resultJson['total'] = $service->getRepository()->countResult(['type' => $type->getId()]);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'type' => $type,
            'event' => $entities,
        ]);
    }

    /**
     * @return mixed|JsonModel|ViewModel
     * @throws Exception\TagNotFoundException
     */
    public function tagAction()
    {
        $request = $this->getRequest();
        $viewQuery = $this->params()->fromQuery('viewHtml', 0);
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        $page    = $this->params()->fromQuery('page', 1);
        $limit   = $this->params()->fromQuery('limit', $this->getOptions()->getArchiveListingLimit());
        $offsetParam  = $this->params()->fromQuery('offset');
        $offset  = isset($offsetParam) ? $offsetParam : ($page - 1) * $limit;

        $sort = $this->params()->fromQuery('sort', 'id');
        $dir = $this->params()->fromQuery('dir', 'desc');

        $id = $this->params()->fromRoute('alias', '');

        if ($id === '') {
            return $this->forward()->dispatch('Event\Controller\Event', ['action' => 'index']);
        }

        $service = $this->getEventService();

        try {
            $tagService = $this->getTagService();
            $tag = $tagService->findOneBy(['alias' => $id]);

            if (!$tag) {
                $message = sprintf(_('Tag %s introuvable'), $id);
                throw new Exception\TagNotFoundException($message);
            }

            $entities = $service->getRepository()->findEventByTags($tag->getId(), true, $sort, $dir, $limit, $offset);
        } catch (Exception\TagNotFoundException $e) {
            $errMessage = $e->getMessage();
            $service->getLogger()->warn($errMessage);

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage($errMessage);
            return $this->forward()->dispatch('Blog\Controller\Article', ['action' => 'index']);
        }

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($entities, 'json', 'details');

            if ($viewQuery) {
                $htmlViewPart = new ViewModel();
                $htmlViewPart->setTerminal(true)
                    ->setTemplate('event/_partials/paginate/event')
                    ->setVariables(array(
                        'event' => $entities,
                    ));

                $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
                $htmlOutput = $viewRenderer->render($htmlViewPart);

                $resultJson['html'] = $htmlOutput;
            }
            //$resultJson['total'] = $service->getRepository()->countResult(['tags' => $tag->getId()]);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'tag' => $tag,
            'event' => $entities,
        ]);
    }

    /**
     * @return FeedModel
     */
    public function feedAction()
    {
        $limit    = $this->getOptions()->getFeedListingLimit();
        $repository = $this->getEventService()->getRepository();
        $events = $repository->findEvents(true, 'publishDate', $dir = 'desc', $limit);
        $model = new FeedModel;
        $model->setOption('feed_type', $this->params('type', 'rss'));
        // Convert articles listing into feed
        $feedSettings       = $this->getOptions()->getFeedSettings();
        $model->title       = $feedSettings['title'];
        $model->description = $feedSettings['description'];
        $model->link        = $this->url()->fromRoute('event', array(), array('force_canonical' => true));
        $model->feed_link   = array(
            'link' => $this->url()->fromRoute('event/feed', array(), array('force_canonical' => true)),
            'type' => $this->params('type', 'rss'),
        );
        if (null !== ($generator = $this->getOptions()->getFeedGenerator())) {
            $model->generator = $generator;
        }
        $entries   = array();
        $modified  = new \DateTime('@0');
        foreach ($events as $event) {
            $entry = array(
                'title'        => $event->getName(),
                'description'  => $event->getDetails(),
                'date_created' => $event->getCreatedAt(),
                'link'         => $this->url()->fromRoute(
                    'event/view',
                    array('alias' => $event->getAlias()),
                    array('force_canonical' => true)
                ),
                //        author' => array(
                //             'name'  => 'WebMaster SantéFuté',
                //             'email' => 'jurian@juriansluiman.nl', // optional
                //             'uri'   => 'http://juriansluiman.nl', // optional
                //         ),
            );
            if ($event->getUpdatedAt() > $modified) {
                $modified = $event->getUpdatedAt();
            }
            $entries[] = $entry;
        }
        $model->entries       = $entries;
        $model->date_modified = $modified;
        return $model;
    }

    /**
     * @param EventServiceInterface $eventService
     * @return $this
     */
    public function setEventService(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
        return $this;
    }

    /**
     * @return EventServiceInterface
     */
    public function getEventService()
    {
        return $this->eventService;
    }

    /**
     * @param TypeServiceInterface $typeService
     * @return $this
     */
    public function setTypeService(TypeServiceInterface $typeService)
    {
        $this->typeService = $typeService;
        return $this;
    }

    /**
     * @return TypeServiceInterface
     */
    public function getTypeService()
    {
        return $this->typeService;
    }

    /**
     * @param TagServiceInterface $tagService
     * @return $this
     */
    public function setTagService(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;
        return $this;
    }

    /**
     * @return TagServiceInterface
     */
    public function getTagService()
    {
        return $this->tagService;
    }

    /**
     * @param InputFilterInterface $inputFilter
     * @return EventController
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        return $this;
    }

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * set options
     *
     * @param ModuleOptionsInterface $options
     * @return EventController
     */
    public function setOptions(ModuleOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get options
     *
     * @return ModuleOptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

}