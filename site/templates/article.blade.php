@snippet('header')

{{-- article --}}
<article class="max-w-screen-lg bg-red-300 h-full mt-48 px-8 mx-auto flex flex-col">

  {{-- header article --}}
  <header class="flex flex-col justify-center content-center">

    {{-- cover --}}
    <picture>
      <source
        srcset="<?= $cover->srcset('avif') ?>"
        sizes="<?= $sizes ?>"
        type="image/avif"
      >
      <source
        srcset="<?= $cover->srcset('webp') ?>"
        sizes="<?= $sizes ?>"
        type="image/webp"
      >
      <img
        alt="<?= $cover->alt() ?>"
        src="<?= $cover->resize(300)->url() ?>"
        srcset="<?= $cover->srcset() ?>"
        sizes="<?= $sizes ?>"
        width="<?= $cover->resize(1800)->width() ?>"
        height="<?= $cover->resize(1800)->height() ?>"
      >
    </picture>

    {{-- meta --}}
    <dl class="flex flex-row">
      <dt>Date</dt>
      <dd>{{ strtoupper($page->date()->toDate('d F Y')) }}</dd>

      <dt>Author</dt>
      <dd>{{ strtoupper($page->author()) }}</dd>

      <dt>Image by</dt>
      <dd>{{ strtoupper($page->imagesBy()) }}</dd>

      <dt>Categories</dt>
      <dd>{{ strtoupper($page->categories()) }}</dd>
    </dl>

    {{-- main info --}}
    <section>
      <h1 class="font-bold text-8xl">{{ $page->title() }}</h1>
      <ul class="flex flex-row space-x-2">
        @foreach($page->tags()->split() as $tag)
        <li class="border-black border px-3 py-1 rounded-full hover:bg-gray-200">
          <a href="{{ $page->url() }}">{{ $tag }}</a>
        </li>
        @endforeach
      </ul>
    </section>

  </header>

  {{-- article layout --}}
  <main class="bg-blue-200">
    @foreach ($page->article()->toLayouts() as $layout)
    <section id="{{ $layout->id() }}">
      @foreach ($layout->columns() as $column)
        @foreach ($column->blocks() as $block)
        <span class="block-type-{{ $block->type() }}">
          @snippet('blocks/' . $block->type(), ['block' => $block, 'layout' => $layout])
        </span>
        @endforeach
     @endforeach
    </section>
    @endforeach
  </main>


    {{-- related articles --}}
    <footer>
      Related...
    </footer>

</article>

@snippet('footer')
