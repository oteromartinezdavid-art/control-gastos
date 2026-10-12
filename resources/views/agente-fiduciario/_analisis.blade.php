@php
    $vClase = [
        'Comprar'  => 'bg-emerald-100 text-emerald-700',
        'Vigilar'  => 'bg-indigo-100 text-indigo-700',
        'Mantener' => 'bg-amber-100 text-amber-700',
        'Evitar'   => 'bg-red-100 text-red-700',
    ][$a->veredicto] ?? 'bg-gray-100 text-gray-600';
    $sClase = $a->score >= 70 ? 'text-emerald-600' : ($a->score >= 45 ? 'text-amber-600' : 'text-red-600');
@endphp
<div class="flex items-center gap-3 mb-2">
    <span class="text-3xl font-black {{ $sClase }}">{{ $a->score ?? '—' }}</span>
    <span class="text-[10px] text-gray-400 uppercase">/100</span>
    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase {{ $vClase }}">{{ $a->veredicto ?? '—' }}</span>
    @if($a->precio_objetivo)
        <span class="ml-auto text-xs text-gray-500">Obj: <b>{{ number_format($a->precio_objetivo, 2, ',', '.') }}</b></span>
    @endif
</div>
<p class="text-sm text-gray-700 mb-2">{{ $a->tesis }}</p>
@if(!empty($a->riesgos))
    <div class="mb-2">
        <p class="text-[11px] font-bold uppercase text-gray-400">Riesgos</p>
        <ul class="list-disc ml-4 text-xs text-gray-600">
            @foreach($a->riesgos as $r)<li>{{ $r }}</li>@endforeach
        </ul>
    </div>
@endif
@if(!empty($a->banderas_rojas))
    <div class="mb-2">
        <p class="text-[11px] font-bold uppercase text-red-500">Banderas rojas</p>
        <ul class="list-disc ml-4 text-xs text-red-600">
            @foreach($a->banderas_rojas as $r)<li>{{ $r }}</li>@endforeach
        </ul>
    </div>
@endif
<p class="text-[10px] text-gray-300 mt-2">{{ $a->modelo }} · ${{ number_format($a->coste, 4) }} · {{ $a->created_at->diffForHumans() }}</p>
