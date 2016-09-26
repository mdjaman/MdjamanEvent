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

return array(
    'service_manager' => array(
        'factories' => array(
            'Event\Options\ModuleOptions'        => \MdjamanEvent\Factory\Options\ModuleOptionsFactory::class,

            'Event\Service\Event'                => \MdjamanEvent\Factory\Service\EventServiceFactory::class,
            'Event\Service\Type'                 => \MdjamanEvent\Factory\Service\TypeServiceFactory::class,
            'Event\Service\Tag'                  => \MdjamanEvent\Factory\Service\TagServiceFactory::class,

            'Event\Repository\Event'             => \MdjamanEvent\Factory\Repository\EventRepositoryFactory::class,
            'Event\Repository\Type'              => \MdjamanEvent\Factory\Repository\TypeRepositoryFactory::class,
            'Event\Repository\Tag'               => \MdjamanEvent\Factory\Repository\TagRepositoryFactory::class,

            'Event\Filter\Event'                 => \MdjamanEvent\Factory\Filter\EventFilterFactory::class,

            'EventAdmin\Form\Event'              => \MdjamanEventAdmin\Factory\Form\EventFormFactory::class,
        ),
        'invokables' => array(
            'Event\Filter\Type'                  => \MdjamanEvent\Filter\TypeFilter::class,
            'Event\Filter\Tag'                   => \MdjamanEvent\Filter\TagFilter::class,
        )
    ),

    'controllers' => array(
        'factories' => array(
            'Event\Controller\Event'                => \MdjamanEvent\Factory\Controller\EventControllerFactory::class,
            'Event\Controller\Type'                 => \MdjamanEvent\Factory\Controller\TypeControllerFactory::class,

            'EventAdmin\Controller\Event'           => \MdjamanEventAdmin\Factory\Controller\EventControllerFactory::class,
            'EventAdmin\Controller\Type'            => \MdjamanEventAdmin\Factory\Controller\TypeControllerFactory::class,
            'EventAdmin\Controller\Tag'             => \MdjamanEventAdmin\Factory\Controller\TagControllerFactory::class,
            'EventAdmin\Controller\Upload'          => \MdjamanEventAdmin\Factory\Controller\UploadControllerFactory::class,
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    'view_helpers' => array(
        'factories'  => array(

        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'event_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Event/Entity',
                ),
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Event\Entity' => 'event_driver',
                ),
            ),
        ),
    ),

    'jms_serializer' => array(
        'naming_strategy' => 'identical'
    ),

    'router' => [
        'routes' => [
            'event' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/event',
                    'defaults' => [
                        '__NAMESPACE__' => 'MdjamanEvent\Controller',
                        'controller' => 'Event',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'event' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/event',
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'view' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/[:id]',
                                    'defaults' => [
                                        'action' => 'view',
                                    ],
                                    'constraints' => [
                                        'id'  => '[a-zA-Z0-9-_.]+',
                                    ],
                                ],
                            ],
                            'type' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/type/[:alias]',
                                    'defaults' => array(
                                        'action' => 'view',
                                    ),
                                    'constraints' => array(
                                        'alias' => '[a-zA-Z0-9-_.]+',
                                    ),
                                ),
                            ),
                            'tag' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/tag/[:alias]',
                                    'defaults' => array(
                                        'controller' => 'Event',
                                        'action' => 'tag',
                                    ),
                                    'constraints' => array(
                                        'alias' => '[a-zA-Z0-9-_.]+',
                                    ),
                                ),
                            ),
                            'feed' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'    => '/feed[/:type]',
                                    'defaults' => array(
                                        'controller' => 'Event',
                                        'action' => 'feed',
                                    ),
                                    'constraints' => array(
                                        'type' => '(rss|atom)',
                                    ),
                                ),
                            ),
                        ],
                    ],
                ],
            ],
            
            'zfcadmin' => [
                'child_routes' => [
                    'event' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/event',
                            'defaults' => [
                                '__NAMESPACE__' => 'MdjamanEventAdmin\Controller',
                                'controller' => 'Event',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'event' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/event',
                                    'defaults' => [
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'view' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/[:id]',
                                            'defaults' => [
                                                'action' => 'view',
                                            ],
                                            'constraints' => [
                                                'id' => '[a-zA-Z-0-9-]*'
                                            ],
                                        ],
                                    ],
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/_new',
                                            'defaults' => array(
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_edit',
                                            'defaults' => array(
                                                'action' => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_delete',
                                            'defaults' => array(
                                                'action' => 'delete',
                                            ),
                                        ),
                                    ),
                                ],
                            ],
                            'type' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/type',
                                    'defaults' => [
                                        'controller' => 'Type',
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'view' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/[:id]',
                                            'defaults' => [
                                                'action' => 'view',
                                            ],
                                            'constraints' => [
                                                'id' => '[a-zA-Z-0-9-]*'
                                            ],
                                        ],
                                    ],
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/_new',
                                            'defaults' => array(
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_edit',
                                            'defaults' => array(
                                                'action' => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_delete',
                                            'defaults' => array(
                                                'action' => 'delete',
                                            ),
                                        ),
                                    ),
                                ],
                            ],
                            'tag' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/tag',
                                    'defaults' => [
                                        'controller' => 'Tag',
                                        'action' => 'index',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'view' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/[:id]',
                                            'defaults' => [
                                                'action' => 'view',
                                            ],
                                            'constraints' => [
                                                'id' => '[a-zA-Z-0-9-]*'
                                            ],
                                        ],
                                    ],
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/_new',
                                            'defaults' => array(
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_edit',
                                            'defaults' => array(
                                                'action' => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/[:id]/_delete',
                                            'defaults' => array(
                                                'action' => 'delete',
                                            ),
                                        ),
                                    ),
                                ],
                            ],
                            'upload' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/upload',
                                    'defaults' => [
                                        'controller' => 'Upload',
                                        'action' => 'upload',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'navigation' => array(
        'admin' => array(
            'event' => array(
                'label' => 'Evènements',
                'route' => 'zfcadmin/event/event',
                'icon'  => 'fa-calendar',
                'order' => 4,
                'pages' => array(
                    'event' => array(
                        'label' => 'Evènements',
                        'route' => 'zfcadmin/event/event',
                        'icon'  => 'fa-calendar',
                    ),
                    'create' => array(
                        'label' => 'Nouvel évènement',
                        'route' => 'zfcadmin/event/event/add',
                        'icon'  => 'fa fa-calendar-plus-o',
                    ),
                    'type' => array(
                        'label' => 'Rubriques',
                        'route' => 'zfcadmin/event/type',
                        'icon'  => 'fa-folder-o',
                    ),
                    'tag' => array(
                        'label' => 'Mots-clés',
                        'route' => 'zfcadmin/event/tag',
                        'icon'  => 'fa-tag',
                    ),
                ),
            ),
        ),
    ),
);