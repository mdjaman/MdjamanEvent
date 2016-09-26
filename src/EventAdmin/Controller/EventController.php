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

namespace MdjamanEventAdmin\Controller;

use MdjamanEvent\Exception;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEvent\Service\EventServiceInterface;
use MdjamanEventAdmin\Form\EventFormInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Class EventController
 * @package EventAdmin\Controller
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class EventController extends AbstractActionController
{

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @var ModuleOptionsInterface
     */
    protected $options;

    /**
     * @var EventFormInterface
     */
    protected $eventForm;

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
        $limit = $this->params()->fromQuery('limit', $this->getOptions()->getAdminListingLimit());
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

        return new ViewModel([
            'event' => $events,
        ]);
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
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Event', ['action' => 'index']);
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
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Event', ['action' => 'index']);
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
     * Add an event
     * @return \Zend\Http\Response|JsonModel|ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $form = $this->getEventForm();
        $service = $this->getEventService();
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($service->createEntity());

            $form->setData($data);
            if ($form->isValid()) {
                try {
                    $event = $service->saveEventWithTags($form->getData(), $data['hidden-tags']);
                } catch (\Exception $ex) {
                    $msg = sprintf(
                        "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
                    );
                    $service->getLogger()->warn($msg);
                    $errMsg = $ex->getMessage();

                    $this->flashMessenger()->addMessage($errMsg);

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['msg'] = $errMsg;
                        return new JsonModel($resultJson);
                    }

                    return new ViewModel([
                        'form' => $form,
                    ]);
                }

                $message = sprintf(_('Evènement %s ajouté avec succès'), $event->getName());
                if ($request->isXmlHttpRequest()) {
                    $resultJson['code'] = 1;
                    $resultJson['msg'] = $message;
                    $resultJson['data'] = $service->serialize($event);
                    return new JsonModel($resultJson);
                }

                $this->flashMessenger()->addMessage($message);
                return $this->redirect()->toRoute('zfcadmin/event/event');
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonModel($resultJson);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    /**
     * Update an event
     * @return \Zend\Http\Response|JsonModel|ViewModel
     */
    public function editAction()
    {
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        $service = $this->getEventService();

        try {
            $document = $service->find($id);
            if (!$document) {
                throw new Exception\EventNotFoundException(sprintf(_('Evènement %s introuvable'), $id), 404);
            }
        } catch (Exception\EventNotFoundException $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);

            $errMsg = $ex->getMessage();
            $this->flashMessenger()->addMessage($errMsg);

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $ex->getMessage();
                return new JsonModel($resultJson);
            }

            return $this->redirect()->toRoute('zfcadmin/event/event');
        }

        $form = $this->getEventForm();
        $form->bind($document);

        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                try {
                    $event = $service->saveEvent($form->getData());
                    if (!$event) {
                        throw new Exception\InvalidArgumentException(sprintf(_('Echec mise à jour de l\'évènement %s'), $id), 500);
                    }

                    $message = sprintf(_('Evènement %s mis à jour avec succès'), $event->getName());

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['code'] = 1;
                        $resultJson['msg'] = $message;
                        $resultJson['data'] = $service->serialize($event);
                        return new JsonModel($resultJson);
                    }

                    return $this->redirect()->toRoute('zfcadmin/event/event/view', [
                        'id' => $event->getId(),
                    ]);
                } catch (Exception\InvalidArgumentException $ex) {
                    $msg = sprintf(
                        "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
                    );
                    $service->getLogger()->err($msg);

                    $errMsg = $ex->getMessage();
                    $this->flashMessenger()->addMessage($errMsg);

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['msg'] = $errMsg;
                        return new JsonModel($resultJson);
                    }

                    return new ViewModel(array(
                        'form' => $form,
                        'event' => $document,
                    ));
                }
            }

            return new JsonModel($resultJson);
        }

        return new ViewModel(array(
            'form' => $form,
            'event' => $document
        ));
    }

    /**
     * Delete an event
     * @return mixed|\Zend\Http\Response|JsonModel|ViewModel
     */
    public function deleteAction()
    {
        $service = $this->getEventService();
        $id = $this->params()->fromRoute('id', 0);
        $request = $this->getRequest();
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        try {
            $document = $service->find($id);
            if (!$document) {
                throw new Exception\EventNotFoundException(sprintf('Evènement %s introuvable', $id), 404);
            }
        } catch (Exception\EventNotFoundException $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);
            $errMessage = $ex->getMessage();

            $this->flashMessenger()->addMessage($errMessage);

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Event', array('action' => 'index'));
        }

        if ($request->isPost()) {
            $del = $request->getPost('delete', 'no');
            if ($del == 'yes') {
                $id = $request->getPost('id');
                try {
                    $service->delete($document, true);

                    $message = sprintf(_('Evènement %s supprimé avec succès'), $document->getId());
                    if ($request->isXmlHttpRequest()) {
                        $resultJson['code'] = 1;
                        $resultJson['msg'] = $message;
                        $resultJson['data'] = $service->serialize($document);
                        return new JsonModel($resultJson);
                    }
                } catch (\Exception $e) {
                    $msg = sprintf(_('Suppression évènement %s impossible'), $id);
                    $service->getLogger()->warn($msg);
                    $this->flashMessenger()->addMessage($msg);

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['msg'] = $msg;
                        return new JsonModel($resultJson);
                    }

                    return $this->redirect()->toRoute('zfcadmin/event/event');
                }
            }

            return $this->redirect()->toRoute('zfcadmin/event/event');
        }

        return new ViewModel(array(
            'id' => $id,
            'event' => $document,
        ));
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

    /**
     * @return EventFormInterface
     */
    public function getEventForm()
    {
        return $this->eventForm;
    }

    /**
     * @param EventFormInterface $eventForm
     * @return EventController
     */
    public function setEventForm(EventFormInterface $eventForm)
    {
        $this->eventForm = $eventForm;
        return $this;
    }

}