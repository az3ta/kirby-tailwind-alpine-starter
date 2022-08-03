@snippet('header')

<main class="mt-48 w-full flex flex-col items-center">

  <section class="max-w-prose text-center text-2xl">
    @kt($page->about())
  </section>

  <section class="grid grid-cols-4 gap-4 bg-green-400 w-full px-8">

    @foreach ($kirby->users()->role('author')->sort('username', 'asc') as $author)
    <div class="bg-red-200">
      <div>
         <h2>{{ $author->username() }}</h2>
        <p>{{ $author->workRole() }}</p>
        <a href="{{ $author->url() }}">View Profile</a>
      </div>

    </div>
    @endforeach

  </section>
</main>

@snippet('footer')
