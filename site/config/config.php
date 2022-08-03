<?php

return [
    'debug' => true,
    'panel' => [
      'install' => 'true',
      'slug' => 'admin-area'
    ],
    'thumbs' => [
      'srcsets' => [
        'default' => [
          '300w'  => ['width' => 300],
          '600w'  => ['width' => 600],
          '900w'  => ['width' => 900],
          '1200w' => ['width' => 1200],
          '1800w' => ['width' => 1800]
        ],
        'webp' => [
          '300w'  => ['width' => 300, 'format' => 'webp'],
          '600w'  => ['width' => 600, 'format' => 'webp'],
          '900w'  => ['width' => 900, 'format' => 'webp'],
          '1200w' => ['width' => 1200, 'format' => 'webp'],
          '1800w' => ['width' => 1800, 'format' => 'webp']
        ],
      'format' => 'webp',
      'quality'   => 80,
      'driver' => 'im'
      ],
    ],
    'routes' => [
      [
        'pattern' => '/home/{slug}',
        'action'  => function () {
          return page('/{slug}');
        }
      ],
    ],
  ];



