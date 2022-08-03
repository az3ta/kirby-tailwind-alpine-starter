<nav class="flex flex-row w-full justify-between fixed top-8 px-8 items-start">
  <h1>
    <a href="{{ $site->url() }}">
      @svg('assets/logo/logo-black.svg')
    </a>
  </h1>
  <button
    role="button"
    type="button"
    x-text="openMenu ? 'CLOSE' : 'MENU'"
    class="hover:text-gray-600"
    @click="openMenu = !openMenu">
  </button>
</nav>

@snippet('menu')
@snippet('bgColor')

