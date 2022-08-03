@snippet('header')

<main class="m-8 grid grid-cols-2 gap-4 mt-48 text-2xl">
  <section>
    img
  </section>

  <section class="flex flex-col">
    @kt($page->about())

    @if ($page->metaverse()->isNotEmpty())
    <a
      href="{{ $page->metaverse() }}"
      target="_blank"
      rel="noopener noreferrer"
      class="text-3xl font-bold hover:text-white mt-24"
      >ENTER THE METAVERSE
    </a>
    @endif

    <div class="mt-20">
      <h2 class="font-bold">CONTACT US</h2>
      <ul class="grid grid-cols-2">
        <li class="w-full">
          <h4>EDITORIAL</h4>
          @html($page->editorial())
        </li>
        <li>@html($page->admin())</li>
        <li>
          <a href="https://www.instagram.com/{{ $page->ig() }}" target="_blank" rel="noopener noreferrer">@ {{$page->ig() }}</a>
        </li>
        <li>@kt($page->address())</li>
        <li>P.IVA {{ $page->iva() }}</li>
      </ul>
    </div>
  </section>
</main>

@snippet('footer')
