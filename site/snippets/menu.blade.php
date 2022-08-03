<div
  x-cloak
  x-show="openMenu"
  @click.outside="openMenu = false"
  class="h-full bg-gray-100 w-[500px] top-0 right-0 fixed z-10 p-8">
  <nav class="w-full">
    <button
    role="button"
    type="button"
    x-text="openMenu ? 'CLOSE' : 'MENU'"
    @click="openMenu = !openMenu"
    class="fixed right-8 top-8 hover:text-gray-600">
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
          @foreach ($site->children()->listed() as $child)
            <li>
              <a class="hover:text-gray-600" href="{{ $child->url() }}">{{ $child->title() }}</a>
            </li>
          @endforeach
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
