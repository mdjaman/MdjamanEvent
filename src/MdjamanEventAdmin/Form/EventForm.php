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

namespace MdjamanEventAdmin\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use MdjamanEvent\Options\ModuleOptionsInterface;
use MdjamanCommon\Form\BaseForm;

/**
 * Description of EventForm
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class EventForm extends BaseForm implements EventFormInterface
{

    /**
     * EventForm constructor.
     * @param ObjectManager $om
     * @param ModuleOptionsInterface $moduleOptions
     */
    public function __construct(ObjectManager $om, ModuleOptionsInterface $moduleOptions)
    {
        $entityClass = $moduleOptions->getEventEntityClass();
        parent::__construct('event', true);
        $this->setHydrator(new DoctrineObject($om))
             ->setObject(new $entityClass);

        $this->add([
            'name' => 'name',
            'attributes' => [
                'required' => 'required',
                'placeholder' => _('Titre'),
                'id' => 'name'
            ],
            'options' => [
                'label' => _('Titre'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'details',
            'attributes' => [
                'type' => 'textarea',
                'placeholder' => _('Texte'),
                'id' => 'details',
                'class' => 'ckeditor',
            ],
            'options' => [
                'label' => _('Texte'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'address',
            'attributes' => [
                'type' => 'textarea',
                'placeholder' => _('Adresse'),
                'id' => 'address',
            ],
            'options' => [
                'label' => _('Adresse'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'startDate',
            'attributes' => [
                'type' => 'text',
                'placeholder' => _('JJ-MM-AAAA'),
                'id' => 'startDate',
                'class' => 'datepicker',
            ],
            'options' => [
                'label' => _('Date début'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'endDate',
            'attributes' => [
                'type' => 'text',
                'placeholder' => _('JJ-MM-AAAA'),
                'id' => 'endDate',
                'class' => 'datepicker',
            ],
            'options' => [
                'label' => _('Date fin'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'type',
            'type' => ObjectSelect::class,
            'attributes' => [
                'required' => false,
                'id' => 'type',
                'class' => 'chzn-select',
            ],
            'options' => [
                'label' => _('Type'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'empty_option' => _('-- Choix type --'),
                'object_manager' => $om,
                'target_class' => $moduleOptions->getTypeEntityClass(),
                'property' => 'name',
                'is_method' => true,
                'find_method' => [
                    'name' => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy' => ['name' => 'ASC'],
                    ],
                ],
                'twb-layout' => 'horizontal',
            ],
        ]);
        $this->add([
            'name' => 'tags',
            'attributes' => [
                'placeholder' => _('Mots-clés'),
                'id' => 'tags',
                'class' => 'tm-input',
            ],
            'options' => [
                'label' => _('Mots-clés'),
                'help-block' => _('Utilisez les touches TAB ou VIRGULE comme séparateurs'),
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'img',
            'attributes' => [
                'id' => 'img',
            ],
            'options' => [
                'label' => 'Image',
                'column-size' => 'sm-10',
                'label_attributes' => array('class' => 'col-sm-2'),
                'twb-layout' => 'horizontal',
            ]
        ]);
        $this->add([
            'name' => 'active',
            'type' => 'checkbox',
            'attributes' => [
                'value' => 1,
            ],
            'options' => [
                'label' => _('Activer'),
                'column-size' => 'sm-10 col-sm-offset-2',
                'twb-layout' => 'horizontal',
            ],
        ]);
        $this->add([
            'name' => 'feature',
            'type' => 'checkbox',
            'attributes' => [
                'value' => 0,
            ],
            'options' => [
                'label' => _('Mettre en avant'),
                'column-size' => 'sm-10 col-sm-offset-2',
                'twb-layout' => 'horizontal',
            ],
        ]);
        $this->add([
            'name' => 'cmtopen',
            'type' => 'checkbox',
            'attributes' => [
                'value' => 0,
            ],
            'options' => [
                'label' => _('Autoriser les commentaires'),
                'column-size' => 'sm-10 col-sm-offset-2',
                'twb-layout' => 'horizontal',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'button',
            'attributes' => array('type' => 'submit'),
            'options' => array(
                'ignore' => true,
                'label' => _('Enregistrer'),
                'column-size' => 'sm-10 col-sm-offset-2',
                'twb-layout' => 'horizontal',
            )
        ]);
    }
}
