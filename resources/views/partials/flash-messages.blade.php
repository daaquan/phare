@if ($flash->has())
  <div class="toast toast-center toast-top">
    @foreach ($flash->getMessages() as $type => $messages)
      <div class="alert alert-{{ $type }} rounded-lg">
        @foreach ($messages as $message)
          <span>{{ $message }}</span>
        @endforeach
      </div>
    @endforeach
  </div>
@endif
