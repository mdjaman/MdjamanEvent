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

use MdjamanCommon\Image\Processor;
use MdjamanEvent\Definitions;
use MdjamanEvent\Entity\EventInterface;
use MdjamanEvent\Service\EventServiceInterface;
use Zend\File\Transfer\Adapter\Http;
use Zend\Form\FormInterface;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * UploadController
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class UploadController extends AbstractActionController
{

    /**
     * @var string
     */
    protected $uploadsDir;

    /**
     * @var FormInterface
     */
    protected $uploadForm;

    /**
     * @var array
     */
    protected $imageExt = ['png', 'jpg', 'jpeg', 'bmp'];

    /**
     * @var array
     */
    protected $resizes = [
        'thumb' => [280, 150],
        'medium' => [400, 250],
        'mini' => [130, 60],
        'resize' => [600, 400]
    ];

    /**
     * @var EventServiceInterface
     */
    protected $eventService;

    /**
     * @return mixed
     */
    public function uploadAction()
    {
        $request = $this->getRequest();
        $files = $request->getFiles();
        
        if (!count($files)) {
            throw new \InvalidArgumentException('Filenames should be provided');
        }

        return $this->uploadToFolder();
    }

    /**
     * Upload files to folder
     * @return mixed
     */
    public function uploadToFolder()
    {
        $request = $this->getRequest();
        $post = array_merge_recursive(
                $request->getPost()->toArray(), 
                $request->getFiles()->toArray()
        );

        $form = $this->getUploadForm();
        $form->setData($post);
        
        if ($form->isValid()) {
            $destination = $this->getUploadsDir();
            $adapter = new Http();
            $adapter->setOptions([
                'destination' => $destination,
                'overwrite' => true,
            ]);
            
            $datas = [];

            $type = Definitions::FOLDER_NAME;
            foreach ($adapter->getFileInfo() as $info) {
                $ext = pathinfo($info['name'], PATHINFO_EXTENSION);
                $newName = $type . '/' . md5(rand(). $info['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', [
                    'target' => $destination . '/' . $newName,
                ]);
                
                if (!$adapter->receive($info['name'])) {
                    continue;
                }
                
                $file = $adapter->getFilter('File\Rename')->getFile();
                
                $file[0]['name'] = $info['name'];
                $file[0]['size'] = $info['size'];
                $file[0]['type'] = $info['type'];
                
                $target = $file[0]['target'];
                $fileclass = $this->buildFileClass($file[0], $type);
                
                $id = $this->params()->fromQuery('id');
                if ($id) {
                    $this->saveToDB($fileclass->url, $id);
                }

                $noresizeParam = $this->params()->fromQuery('noresize', 0);
                $resizeParam = $this->params()->fromQuery('resize');
                
                if ($this->isImage($target)) {
                    switch (true) {
                        case $noresizeParam === 1:
                            $resizes = [];
                            break;
                        case isset($resizeParam):
                            $resizes = explode(',', $resizeParam);
                            break;
                        default:
                            $resizes = isset($this->resizes[$type]) ? $this->resizes[$type] : [];
                    }
                    
                    $this->imageProcessor($target, $resizes);
                }
                
                array_push($datas, $fileclass);
            }
        }
        
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/json');
        $headers->addHeaderLine('Pragma', 'no-cache');
        $headers->addHeaderLine('Cache-Control', 'private, no-cache');
        $headers->addHeaderLine('Content-Disposition', 'inline; filename="files.json"');
        $headers->addHeaderLine('X-Content-Type-Options', 'nosniff');
        $headers->addHeaderLine('Vary', 'Accept');
        $response->setHeaders($headers);
        
        $toReturn = Json::encode($datas);
        
        $response->setContent($toReturn);
        return $response;
    }
    
    /**
     * Delete a file
     * @param mixed $id
     * @return void
     */
    public function delete($id)
    {
        return $this->saveToDB('', $id);
    }

    /**
     * 
     * @param string $target
     * @param array $resizes
     * @return void
     */
    public function imageProcessor($target, $resizes = array())
    {
        new Processor($target, $resizes);
        return;
    }
    
    /**
     * Check if file is an image
     * @param string $file
     * @return boolean
     */
    protected function isImage($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (in_array($ext, $this->imageExt)) {
            return true;
        }
        
        return false;
    }

    /**
     * @param $filename
     * @param string $type
     * @return string
     */
    public function buildFileLink($filename, $type = 'event')
    {
        return sprintf('/uploads/%s/%s', $type, $filename);
    }

    /**
     * Construct a class with file info
     * 
     * @param array $file
     * @param string $type
     * @return \stdClass
     */
    protected function buildFileClass($file, $type = Definitions::FOLDER_NAME)
    {
        $fileclass = new \stdClass();
        $fileclass->success = str_replace(
                $file['target'], 
                'Téléchargement terminé: ', 
                preg_replace('/\d\//', '', $file['name'])
        );
        $fileclass->name = $file['name'];
        $fileclass->path = $file['target'];
        $fileclass->size = $file['size'];
        $fileclass->type = $file['type'];
        $fileclass->delete_url = '/upload/delete';
        $fileclass->delete_type = 'DELETE';
        
        $newName = pathinfo($file['target'], PATHINFO_FILENAME);
        $newExt = pathinfo($file['target'], PATHINFO_EXTENSION);
        $thumbnailUrl = sprintf('/uploads/%s/%s_thumb.%s', 
                $type, 
                $newName, 
                $newExt
        );
        $filename = $newName . '.' . $newExt;
        $url = $this->buildFileLink($filename, $type);
        
        $fileclass->thumbnail_url = $thumbnailUrl;
        $fileclass->url = $url;
        
        return $fileclass;
    }
    
    /**
     *
     * @param string $name plain text name of renamed file
     * @param string $id record identifier from DB
     * @throws Exception
     */
    public function saveToDB($name, $id)
    {
        $service = $this->getEventService();
        try {
            /* @var $event EventInterface */
            $event = $service->find($id);
            $event->setImg($name);
            $service->saveEvent($event);
        } catch (\Exception $ex) {
            $msg = sprintf(
                "%s:%d %s (%d) [%s]\n", $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getCode(), get_class($ex)
            );
            $service->getLogger()->err($msg);
        }
    }

    /**
     * @return mixed
     */
    public function getUploadsDir()
    {
        if (!$this->uploadsDir) {
            $this->setUploadsDir();
        }

        return $this->uploadsDir;
    }

    /**
     * @param string $uploadsDir
     * @return $this
     */
    public function setUploadsDir($uploadsDir = './public/uploads')
    {
        $this->uploadsDir = $uploadsDir;
        return $this;
    }

    /**
     * @param FormInterface $uploadForm
     * @return $this
     */
    public function setUploadForm(FormInterface $uploadForm)
    {
        $this->uploadForm = $uploadForm;
        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getUploadForm()
    {
        return $this->uploadForm;
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
     * @return UploadController
     */
    public function setEventService(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
        return $this;
    }

}
