@extends('layouts.app')
@section('title', __('common.categories_title') . ' — ' . config('app.name'))
@section('content')

@php
use App\Support\CategoryIcons;
$gradients = [
  'linear-gradient(135deg,#6C2E63,#A6457E)',
  'linear-gradient(135deg,#1F7A6B,#3FB59E)',
  'linear-gradient(135deg,#C2861B,#E8C25A)',
  'linear-gradient(135deg,#2C3E73,#5B7AC9)',
  'linear-gradient(135deg,#A33B4E,#E07C6A)',
  'linear-gradient(135deg,#374151,#6B7787)',
  'linear-gradient(135deg,#4D2049,#8A4A82)',
  'linear-gradient(135deg,#1D6E8C,#54B0C4)',
  'linear-gradient(135deg,#7A5C12,#C9A23E)',
];

@endphp

<div class="wrap cats-wrap">

  {{-- Header --}}
  <div class="cats-header">
    <h1 class="cats-h1">{{ __('common.categories_heading') }}</h1>
    <p class="cats-subtitle">
      {{ __('common.categories_subtitle', ['count' => $categories->count(), 'word' => Str::plural('category', $categories->count())]) }}
    </p>
  </div>

  {{-- Grid --}}
  <div class="cat-grid">
    @forelse($categories as $i => $cat)
    @php $grad = $gradients[$i % count($gradients)]; @endphp
    <a href="{{ route('quizzes.index', ['category' => $cat->slug]) }}" class="cat-card">
      <div class="cat-card-bg" style="background:{{ $grad }};"></div>
      <div class="cat-card-body">
        @php
          $iconKey = CategoryIcons::resolveKey($cat->icon);
          $svg = CategoryIcons::svg($iconKey, 28, '#fff');
        @endphp
        <div class="cat-icon">
          @if($svg)
            {!! $svg !!}
          @else
            <span class="cats-icon-letter">{{ strtoupper(substr($cat->name,0,1)) }}</span>
          @endif
        </div>
        <div class="cats-icon-flex">
          <h3 class="cat-name">{{ $cat->name }}</h3>
          <p class="cat-count">{{ $cat->total_quizzes_count }} {{ Str::plural('quiz', $cat->total_quizzes_count) }}</p>
          {{-- Subcategory chips --}}
          @if($cat->children->isNotEmpty())
            <div class="cat-sub-chips">
              @foreach($cat->children->take(4) as $child)
                <span class="cat-sub-chip">{{ $child->name }}</span>
              @endforeach
              @if($cat->children->count() > 4)
                <span class="cat-sub-chip cat-sub-more">+{{ $cat->children->count() - 4 }} more</span>
              @endif
            </div>
          @endif
        </div>
        <svg class="cat-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </div>
    </a>
    @empty
    <div class="cats-empty">
      <p class="cats-empty-p">{{ __('common.categories_none') }}</p>
    </div>
    @endforelse
  </div>

</div>

<style>
/* Categories page utility classes */
.cats-wrap{padding-top:48px;padding-bottom:72px}
.cats-header{margin-bottom:40px}
.cats-h1{font-size:36px;font-weight:700;margin-bottom:8px}
.cats-subtitle{color:var(--text-muted);font-size:16px}
.cats-icon-letter{font-size:22px;font-weight:700}
.cats-icon-flex{flex:1;min-width:0}
.cats-empty{grid-column:1/-1;text-align:center;padding:80px 24px;color:var(--text-muted)}
.cats-empty-p{font-size:18px}
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;}
.cat-card{position:relative;border-radius:var(--radius-card);overflow:hidden;text-decoration:none;color:#fff;min-height:160px;display:flex;flex-direction:column;justify-content:flex-end;transition:.18s;box-shadow:var(--shadow-card);}
.cat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-raised);}
.cat-card:hover .cat-arrow{transform:translateX(4px);}
.cat-card-bg{position:absolute;inset:0;transition:.3s;}
.cat-card:hover .cat-card-bg{filter:brightness(1.1);}
.cat-card-body{position:relative;padding:20px 22px;display:flex;align-items:center;gap:16px;background:linear-gradient(0deg,rgba(0,0,0,.35) 0%,transparent 100%);}
.cat-icon{width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;flex:none;backdrop-filter:blur(6px);}
.cat-name{font-size:18px;font-weight:700;margin:0 0 4px;line-height:1.2;}
.cat-count{font-size:13px;margin:0;opacity:.8;}
.cat-arrow{flex:none;margin-left:auto;transition:transform .18s;}
.cat-sub-chips{display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;}
.cat-sub-chip{font-size:10px;font-weight:600;padding:2px 7px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:20px;white-space:nowrap;backdrop-filter:blur(4px);}
.cat-sub-more{opacity:.7;}
@media(max-width:600px){
  .cat-grid{grid-template-columns:1fr 1fr;gap:12px;}
  .cat-card{min-height:110px;}
  .cat-icon{width:40px;height:40px;font-size:20px;border-radius:10px;}
  .cat-name{font-size:15px;}
}
@media(max-width:380px){
  .cat-grid{grid-template-columns:1fr;}
}
</style>
@endsection
