@csrf
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full" 
            :value="old('descripcion', $ingreso->descripcion ?? '')" required />
    </div>
    
    <div>
        <x-input-label for="monto" value="Monto" />
        <x-text-input id="monto" name="monto" type="number" step="0.01" class="mt-1 block w-full" 
            :value="old('monto', $ingreso->monto ?? '')" required />
    </div>

    <div>
        <x-input-label for="fuente_ingreso_id" value="Fuente de Ingreso" />
        <select name="fuente_ingreso_id" id="fuente_ingreso_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach($fuentes as $f)
                <option value="{{ $f->id }}" {{ (old('fuente_ingreso_id', $ingreso->fuente_ingreso_id ?? '') == $f->id) ? 'selected' : '' }}>
                    {{ $f->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div>
        <x-input-label for="fecha" value="Fecha" />
        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" 
            :value="old('fecha', $ingreso->fecha ?? date('Y-m-d'))" required />
    </div>
</div>