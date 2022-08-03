@snippet('header')
<main class="flex w-full h-full justify-center mt-48 flex-col px-8">
  <h1 class="font-bold">Articles / Products:</h1>
  <ul>
  @foreach (page('home')->children()->listed() as $content)
    <li class="hover:text-gray-600 border-y border-black hover:bg-gray-50">

      <a href="{{ $content->url() }}">
        <p>Date {{ $content->date()->toDate('d F Y') }}</p>
        <p>Title {{ $content->title() }}</p>
        <p>Author {{ $content->author() }}</p>
        <p>Images by {{ $content->imagesBy() }}</p>
        <p>Categories {{ $content->categories() }}</p>
      </a>

    </li>
  @endforeach
  </ul>
</main>
@snippet('footer')
