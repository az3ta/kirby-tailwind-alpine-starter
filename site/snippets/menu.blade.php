<div
  x-cloak
  x-show="openMenu"
  @click.outside="openMenu = false"
  class="h-full bg-gray-100 w-[470px] top-0 right-0 fixed z-10 p-8">
  <nav class="w-full">
    <button
    role="button"
    type="button"
    x-text="openMenu ? 'Close' : 'Menu'"
    @click="openMenu = !openMenu"
    class="fixed right-8 top-8">
    </button>
  </nav>

  <main>
    <section>
          <input type="search">

    </section>

    <section class="grid grid-cols-2">
      <div>
        <h2>RED-EYE WORLD</h2>
        <menu>
          @for ($site->children()->listed() as $child)
            <li><a href="{{ $child->url() }}">{{ $child->title() }}</a></li>
          @endfor
        </menu>
      </div>
      <div>
        <h2>CATEGORIES</h2>
        <menu>
          <li>cat1</li>
          <li>cat2</li>
          <li>cat3</li>
        </menu>
      </div>
    </section>
  </main>

  <footer class="bottom-8 fixed">
    here goes all info
  </footer>

</div>
