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

namespace MdjamanEvent\Filter;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;
use MdjamanEvent\Definitions;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;

/**
 * Description of EventFilter
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class EventFilter extends InputFilter
{

    /**
     * EventFilter constructor.
     * @param ObjectRepository $typeRepository
     */
    public function __construct(ObjectRepository $typeRepository)
    {
        $this->add(array(
            'name'       => 'name',
            'required'   => true,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => 100,
                    ),
                ),
            ),
        ));
        $this->add(array(
            'name'       => 'active',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                new InArray(array(
                    'haystack' => Definitions::getStatusList()
                ))
            )
        ));
        $this->add(array(
            'name'       => 'feature',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                new InArray(array(
                    'haystack' => Definitions::getStatusList()
                ))
            )
        ));
        $this->add(array(
            'name'       => 'cmtopen',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                new InArray(array(
                    'haystack' => Definitions::getStatusList()
                ))
            )
        ));
        $this->add(array(
            'name'       => 'details',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'HtmlEntities'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name'       => 'address',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name'       => 'startDate',
            'required'   => true,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name'       => 'endDate',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name'       => 'type',
            'required'   => true,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                new ObjectExists(array(
                    'object_repository' => $typeRepository,
                    'fields' => array('id')
                )),
            ),
        ));
        $this->add(array(
            'name'       => 'tags',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name'       => 'id',
            'required'   => false,
            'filters'	 => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
    }
}