@snippet('header')

<main class="m-8 grid grid-cols-2 gap-4 mt-48">
  <section>
    img
  </section>

  <section>
    @kt($page->about())

    @if ($page->metaverse()->isNotEmpty())
    <a
      href="{{ $page->metaverse() }}"
      target="_blank"
      rel="noopener noreferrer"
      >ENTER THE METAVERSE
    </a>
    @endif

    <div>
      <h2>CONTACT US</h2>
      <p>redazione</p>
      <p>admin</p>
      <p>address</p>
      <p>social</p>
    </div>
  </section>
</main>

@snippet('footer')
