<?php

$data = $pages->find('home')->children()->published()->flip();
$data_100 = $data->limit(100);
$json = [];

foreach($data_100 as $article) {

  $json[] = [
    'url'   => (string)$article->url(),
    'title' => (string)$article->title(),
    'cover'  => (string)$article->files()->filterBy('template', 'coverArticle')->first()->url(),
    'bgColor'  => (string)$article->colors(),
    'highlight'  => (string)$article->highlight(),
    'date'  => (string)$article->date(),
    'author'  => (string)$article->author(),
    'imagesBy'  => (string)$article->imagesBy(),
    'categories'  => (array)$article->categories(),
    'tags'  => (string)$article->tags(),
    'link'  => (string)$article->link(),
    'article'  => (string)$article->article(),
  ];

}

echo json_encode($json);
