<nav class="flex flex-row w-full justify-between fixed top-8 px-8">
  <a href="{{ $site->url() }}">RED-EYE</a>
  <button
    role="button"
    type="button"
    x-text="openMenu ? 'Close' : 'Menu'"
    @click="openMenu = !openMenu">
  </button>
</nav>

@snippet('menu')
@snippet('bgColor')

