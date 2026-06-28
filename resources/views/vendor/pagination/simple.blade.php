@if ($paginator->hasPages())
<div class="pager">
  @if ($paginator->onFirstPage())
    <button disabled>‹</button>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost" style="min-width:38px;height:38px;padding:0;display:grid;place-items:center;">‹</a>
  @endif
  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost" style="min-width:38px;height:38px;padding:0;display:grid;place-items:center;">›</a>
  @else
    <button disabled>›</button>
  @endif
</div>
@endif
