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
use MdjamanEvent\Entity\EventInterface;
use MdjamanEvent\Exception\TagNotFoundException;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEvent\Service\TagServiceInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Class TagController
 * @package EventAdmin\Controller
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class TagController extends AbstractActionController
{

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

        $service = $this->getTagService();
        $tags = $service->filters($filteredValues, [$sort => $dir], $limit, $offset);

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($tags);
            $resultJson['total'] = $service->countMatchingRecords($filters);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'tag' => $tags,
        ]);
    }

    /**
     * Fetch an tag
     * @return mixed|JsonModel|ViewModel
     */
    public function viewAction()
    {
        $request = $this->getRequest();
        $service = $this->getTagService();
        $id = $this->params()->fromRoute('id', 0);
        $resultJson = [
            'code' => 0,
            'msg' => 'There was some error. Try again.',
            'data' => null,
        ];

        if ($id === 0) {
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Tag', ['action' => 'index']);
        }

        try {
            $tag = $service->find($id);

            if (!$tag) {
                $message = sprintf(_('Mot-clé %s introuvable'), $id);
                throw new TagNotFoundException($message);
            }

        } catch (TagNotFoundException $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->warn($msg);
            $errMessage = $ex->getMessage();

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $errMessage;
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage(_('Mot-clé introuvable'));
            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Tag', ['action' => 'index']);
        }

        if ($request->isXmlHttpRequest()) {
            $resultJson['code'] = 1;
            $resultJson['msg'] = 'success';
            $resultJson['data'] = $service->serialize($tag);
            return new JsonModel($resultJson);
        }

        return new ViewModel([
            'tag' => $tag,
        ]);
    }

    /**
     * Add an event
     * @return \Zend\Http\Response|JsonModel|ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $service = $this->getTagService();
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

                /* @var $tag EventInterface */
                $tag = $service->saveEvent($filteredValues);
                if (!$tag) {
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

            $message = sprintf(_('Mot-clé %s ajouté avec succès'), $tag->getName());
            if ($request->isXmlHttpRequest()) {
                $resultJson['code'] = 1;
                $resultJson['msg'] = $message;
                $resultJson['data'] = $service->serialize($tag);
                return new JsonModel($resultJson);
            }

            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('zfcadmin/event/tag');
        }

        return new ViewModel();
    }

    /**
     * Delete a tag
     * @return mixed|\Zend\Http\Response|JsonModel|ViewModel
     */
    public function deleteAction()
    {
        $service = $this->getTagService();
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
                throw new TagNotFoundException(sprintf('Mot-clé %s introuvable', $id), 404);
            }
        } catch (TagNotFoundException $e) {
            $service->getLogger()->warn($e->getMessage());
            $this->flashMessenger()->addMessage($e->getMessage());

            if ($request->isXmlHttpRequest()) {
                $resultJson['msg'] = $e->getMessage();
                return new JsonModel($resultJson);
            }

            return $this->forward()->dispatch('MdjamanEventAdmin\Controller\Tag', array('action' => 'index'));
        }

        if ($request->isPost()) {
            $del = $request->getPost('delete', 'no');
            if ($del == 'yes') {
                $id = $request->getPost('id');
                try {
                    $service->delete($document, true);

                    $message = sprintf(_('Mot-clé %s supprimé avec succès'), $document->getId());
                    if ($request->isXmlHttpRequest()) {
                        $resultJson['code'] = 1;
                        $resultJson['msg'] = $message;
                        $resultJson['data'] = $service->serialize($document);
                        return new JsonModel($resultJson);
                    }
                } catch (\Exception $e) {
                    $msg = sprintf(_('Suppression mot-clé %s impossible'), $id);
                    $service->getLogger()->warn($msg);
                    $this->flashMessenger()->addMessage($msg);

                    if ($request->isXmlHttpRequest()) {
                        $resultJson['msg'] = $msg;
                        return new JsonModel($resultJson);
                    }

                    return $this->redirect()->toRoute('zfcadmin/event/tag');
                }
            }

            return $this->redirect()->toRoute('zfcadmin/event/tag');
        }

        return new ViewModel(array(
            'id' => $id,
            'tag' => $document,
        ));
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