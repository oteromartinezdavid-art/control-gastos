@csrf
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full" 
            :value="old('descripcion', $gasto->descripcion ?? '')" required />
    </div>
    
    <div>
        <x-input-label for="monto" value="Monto ($)" />
        <x-text-input id="monto" name="monto" type="number" step="0.01" class="mt-1 block w-full" 
            :value="old('monto', $gasto->monto ?? '')" required />
    </div>
    
    <div>
        <x-input-label for="categoria" value="Categoría" />
        <select name="categoria_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ (old('categoria_id', $gasto->categoria_id ?? '') == $cat->id) ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div>
        <x-input-label for="fecha" value="Fecha" />
        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full" 
            :value="old('fecha', $gasto->fecha ?? date('Y-m-d'))" required />
    </div>
</div>