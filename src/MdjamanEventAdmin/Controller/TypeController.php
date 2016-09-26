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

use Application\Utils\ExceptionUtils;
use MdjamanEvent\Entity\TypeInterface;
use MdjamanEvent\Exception\TypeNotFoundException;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEvent\Service\TypeServiceInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Class TypeController
 * @package EventAdmin\Controller
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class TypeController extends AbstractActionController
{

    /**
     * @var TypeServiceInterface
     */
    protected $typeService;

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
     * Fetch an type
     * @return mixed|JsonModel|ViewModel
     */
    public function viewAction()
    {
        $request = $this->getRequest();
        $service = $this->getTypeService();
        $id = $this->params()->fromRoute('id', 0);
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        if ($id === 0) {
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Type', ['action' => 'index']);
        }

        try {
            $type = $service->find($id);

            if (!$type) {
                $message = sprintf(_('Type %s introuvable'), $id);
                throw new TypeNotFoundException($message);
            }

        } catch (TypeNotFoundException $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);
            $errMessage = $ex->getMessage();

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage(_('Type introuvable'));
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Type', ['action' => 'index']);
        }

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($type);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'type' => $type,
        ]);
    }

    /**
     * Add an event
     * @return \Zend\Http\Response|JsonModel|ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $service = $this->getTypeService();
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        $filter = $this->getInputFilter();

        if ($request->isPost()) {
            try {
                $filter->setData($request->getPost())
                    ->setValidationGroup(InputFilterInterface::VALIDATE_ALL);

                if (!$filter->isValid()) {
                    throw new \Exception(

                        500
                    );
                }

                $filteredValues = $filter->getValues();

                /* @var $type TypeInterface */
                $type = $service->saveType($filteredValues);
                if (!$type) {
                    throw new \Exception(
                        sprintf(ExceptionUtils::PERSISTENCE_ERR, $service->getEntity()),
                        500
                    );
                }
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

                return new ViewModel();
            }

            $message = sprintf(_('Type %s ajouté avec succès'), $type->getName());
            if ($request->isXmlHttpRequest()) {
                $resultJson['code'] = 1;
                $resultJson['msg'] = $message;
                $resultJson['data'] = $service->serialize($type);
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('zfcadmin/event/type');
        }

        return new ViewModel();
    }

    /**
     * Delete a type
     * @return mixed|\Zend\Http\Response|JsonModel|ViewModel
     */
    public function deleteAction()
    {
        $service = $this->getTypeService();
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
                throw new TypeNotFoundException(sprintf('Type %s introuvable', $id), 404);
            }
        } catch (TypeNotFoundException $e) {
            $service->getLogger()->warn($e->getMessage());
            $this->flashMessenger()->addMessage($e->getMessage());

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $e->getMessage();
                return new JsonModel($resultJson);
            }

            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Type', array('action' => 'index'));
        }

        if ($request->isPost()) {
            $del = $request->getPost('delete', 'no');
            if ($del == 'yes') {
                $id = $request->getPost('id');
                try {
                    $service->delete($document, true);

                    $message = sprintf(_('Type %s supprimé avec succès'), $document->getId());
                    if ($request->isXmlHttpRequest()) {
                        $resultJson['code'] = 1;
                        $resultJson['msg'] = $message;
                        $resultJson['data'] = $service->serialize($document);
                        return new JsonModel($resultJson);
                    }
                } catch (\Exception $e) {
                    $msg = sprintf(_('Suppression type %s impossible'), $id);
                    $service->getLogger()->warn($msg);
                    $this->flashMessenger()->addMessage($msg);

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['msg'] = $msg;
                        return new JsonModel($resultJson);
                    }

                    return $this->redirect()->toRoute('zfcadmin/event/type');
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