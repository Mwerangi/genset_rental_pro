@props([
    'paginator',
    'perPage'  => 25,
    'options'  => [10, 25, 50, 100],
])
{{-- Shared pagination bar — used across all index/list views --}}
<div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">

    {{-- Left: showing X–Y of Z + per-page selector --}}
    <div class="flex items-center gap-3 text-sm text-gray-600">
        <span>
            Showing
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>–<strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            of <strong>{{ $paginator->total() }}</strong>
        </span>

        <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
            @foreach(request()->except(['page','per_page']) as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <label class="text-xs text-gray-500">Show</label>
            <select name="per_page" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                @foreach($options as $n)
                <option value="{{ $n }}" @selected((int)$perPage === (int)$n)>{{ $n }}</option>
                @endforeach
            </select>
            <span class="text-xs text-gray-500">per page</span>
        </form>
    </div>

    {{-- Right: group-jump + prev/next --}}
    <div class="flex items-center gap-2">
        @if($paginator->lastPage() > 1)
        <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-1">
            @foreach(request()->except(['page','per_page']) as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <label class="text-xs text-gray-500 whitespace-nowrap">Jump to</label>
            <select name="page" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                @for($p = 1; $p <= $paginator->lastPage(); $p++)
                    @php
                        $from = (($p - 1) * (int)$perPage) + 1;
                        $to   = min($p * (int)$perPage, $paginator->total());
                    @endphp
                    <option value="{{ $p }}" @selected($paginator->currentPage() === $p)>
                        {{ number_format($from) }}–{{ number_format($to) }}
                    </option>
                @endfor
            </select>
        </form>

        @if($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">← Prev</a>
        @endif

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Next →</a>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed">Next →</span>
        @endif
        @endif
    </div>
</div>
