@if ($page->colors()->isNotEmpty())
<style>
  body {
    background-color: {{ $page->colors() }} ;
  }
</style>
@endif
