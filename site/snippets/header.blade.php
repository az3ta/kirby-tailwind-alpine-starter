<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- SEO -->
  @snippet('meta_information')
  @snippet('robots')

  <!-- Tailwind CSS -->
  @css('assets/css/styles.css')

  <!-- Alpine JS -->
  <script defer src="https://unpkg.com/alpinejs@3.10.2/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
  </style>

</head>
<body
  class="font-sans"
  x-data="{ openMenu: false }">

  @snippet('nav')

