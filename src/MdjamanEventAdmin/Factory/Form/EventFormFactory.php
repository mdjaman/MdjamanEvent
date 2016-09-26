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

namespace MdjamanEventAdmin\Factory\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanEventAdmin\Form\EventForm;
use MdjamanEventAdmin\Hydrator\Strategy\TypeStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of EventFormFactory
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class EventFormFactory implements FactoryInterface 
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return EventForm
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $om = $serviceLocator->get('doctrine.entitymanager.orm_default');
        /* @var $options ModuleOptionsInterface */
        $options = $serviceLocator->get('MdjamanEvent\Options\ModuleOptions');
        $filter = $serviceLocator->get('MdjamanEvent\Filter\Event');
        $typeEntityClassName = $options->getTypeEntityClass();

        $hydrator = new DoctrineObject($om);
        $hydrator->addStrategy('type', new TypeStrategy($om, $typeEntityClassName));

        $form = new EventForm($om, $options);
        $form->setInputFilter($filter);
        $form->setHydrator($hydrator);
        return $form;
    }

}
