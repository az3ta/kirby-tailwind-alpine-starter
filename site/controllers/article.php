<?php

return function ($page) {

  $sizes = "(min-width: 1200px) 25vw,
            (min-width: 900px) 33vw,
            (min-width: 600px) 50vw,
            100vw";
  $cover =  $page->files()->filterBy('template', 'coverArticle')->first();

  return [
    'sizes' => $sizes,
    'cover' => $cover,
  ];
};
