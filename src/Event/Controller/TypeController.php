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

use MdjamanEvent\Exception\TypeNotFoundException;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEvent\Service\EventServiceInterface;
use MdjamanEvent\Service\TypeServiceInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Class TypeController
 * @package Event\Controller
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class TypeController extends AbstractActionController
{

    /**
     * @var TypeServiceInterface
     */
    protected $typeService;

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * Fetch list of types
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

        $service = $this->getTypeService();
        $types = $service->filters($filteredValues, [$sort => $dir], $limit, $offset);

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($types);
            $resultJson['total'] = $service->countMatchingRecords($filters);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'type' => $types,
        ]);
    }

    /**
     * Fetch a type
     * @return mixed|JsonModel|ViewModel
     */
    public function viewAction()
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

        $id = $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            return $this->forward()->dispatch('Event\Controller\Type', ['action' => 'index']);
        }

        $service = $this->getTypeService();
        try {
            $type = $service->find($id);

            if (!$type) {
                $message = sprintf(_('Type d\'évènement %s introuvable'), $id);
                throw new TypeNotFoundException($message);
            }

            $eventService = $this->getEventService();
            $entities = $eventService->getRepository()->findArticleByCategory($type, true, null, $sort, $dir, $limit, $offset);
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

            $this->flashMessenger()->addMessage(_('Type d\'évènement introuvable'));
            return $this->forward()->dispatch('Event\Controller\Type', ['action' => 'index']);
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

            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'type' => $type,
            'event' => $entities,
        ]);
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
     * @return EventServiceInterface
     */
    public function getEventService()
    {
        return $this->eventService;
    }

    /**
     * @param EventServiceInterface $eventService
     * @return TypeController
     */
    public function setEventService(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
        return $this;
    }

    /**
     * @param InputFilterInterface $inputFilter
     * @return TypeController
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
     * @return TypeController
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