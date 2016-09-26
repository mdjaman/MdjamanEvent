<?php
/**
 * This file is part of FDFP project.
 * @author Marcel Djaman <marceldjaman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array(
    'service_manager' => array(
        'factories' => array(
            'Event\Options\ModuleOptions'        => \Event\Factory\Options\ModuleOptionsFactory::class,

            'Event\Service\Event'                => \Event\Factory\Service\EventServiceFactory::class,
            'Event\Service\Type'                 => \Event\Factory\Service\TypeServiceFactory::class,
            'Event\Service\Tag'                  => \Event\Factory\Service\TagServiceFactory::class,

            'Event\Repository\Event'             => \Event\Factory\Repository\EventRepositoryFactory::class,
            'Event\Repository\Type'              => \Event\Factory\Repository\TypeRepositoryFactory::class,
            'Event\Repository\Tag'               => \Event\Factory\Repository\TagRepositoryFactory::class,

            'Event\Filter\Event'                 => \Event\Factory\Filter\EventFilterFactory::class,

            'EventAdmin\Form\Event'              => \EventAdmin\Factory\Form\EventFormFactory::class,
        ),
        'invokables' => array(
            'Event\Filter\Type'                  => \Event\Filter\TypeFilter::class,
            'Event\Filter\Tag'                   => \Event\Filter\TagFilter::class,
        )
    ),

    'controllers' => array(
        'factories' => array(
            'Event\Controller\Event'                => \Event\Factory\Controller\EventControllerFactory::class,
            'Event\Controller\Type'                 => \Event\Factory\Controller\TypeControllerFactory::class,

            'EventAdmin\Controller\Event'           => \EventAdmin\Factory\Controller\EventControllerFactory::class,
            'EventAdmin\Controller\Type'            => \EventAdmin\Factory\Controller\TypeControllerFactory::class,
            'EventAdmin\Controller\Tag'             => \EventAdmin\Factory\Controller\TagControllerFactory::class,
            'EventAdmin\Controller\Upload'          => \EventAdmin\Factory\Controller\UploadControllerFactory::class,
        ),
    ),

    'view_manager' => array(
        'template_map'             => include __DIR__  .'/../template_map.php',
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
                        '__NAMESPACE__' => 'Event\Controller',
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
                                '__NAMESPACE__' => 'EventAdmin\Controller',
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