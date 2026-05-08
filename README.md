# Control de Finanzas Personal — Laravel 12

Sistema integral de gestión financiera diseñado para centralizar y automatizar el control de ingresos, gastos, presupuestos, financiaciones e inversiones en una sola plataforma. Sustituye flujos de trabajo en Excel por una aplicación web robusta, dinámica y segura.

---

## Estado del Proyecto

### Fase 1: Dashboard e Interfaz Base ✅

- **Panel de Control Visual:** Resumen mensual en tiempo real de ingresos, gastos ejecutados, gastos fijos pendientes y saldo real disponible.
- **Navegación Temporal:** Filtrado por mes y año mediante Carbon, con navegación entre periodos.
- **Gráficos Estadísticos:** Distribución de gastos por categoría (doughnut) y evolución de gasto diario (línea) con Chart.js.
- **Resúmenes de Módulos:** El dashboard incluye tarjetas de resumen para Financiaciones (deuda activa, cuota mensual) e Inversiones (activos, capital invertido, retorno).
- **Diseño UI/UX:** Interfaz con Tailwind CSS, paleta Indigo/Naranja, Alpine.js para interacciones reactivas.

### Fase 2: Gestión Dinámica y CRUDs ✅

- **Categorías de Gastos:** CRUD completo con asignación de color y presupuesto mensual.
- **Fuentes de Ingresos:** Administración dinámica de procedencias del dinero.
- **Gastos e Ingresos:** Registro manual con filtrado, paginación y edición inline.
- **Integridad Referencial:** Validación que impide borrar categorías con registros vinculados.

### Fase 3: Importador Bancario con Categorización Automática ✅

- **Importación CSV:** Carga masiva de movimientos bancarios (optimizado para extractos Bankinter, formato semicolon-delimited).
- **Prevención de Duplicados:** Hash MD5 por fila (`fecha + descripcion + monto + user_id`) con restricción única en base de datos.
- **Motor de Reglas:** Sistema de palabras clave configurables (almacenadas en mayúsculas) que categorizan automáticamente cada movimiento al importar. Ej: "MERCADONA" → Alimentación.
- **Separación automática:** Importes negativos → Gasto; positivos → Ingreso.

### Fase 4: Gastos Fijos y Financiaciones ✅

- **Gastos Fijos:** Control de suscripciones y pagos recurrentes con detección de pago por coincidencia difusa de descripción. Soporte de `meses_cobro` para pagos no mensuales (anuales, trimestrales, etc.).
- **Financiaciones:** Gestión de préstamos y pagos a plazos. Seguimiento de cuotas pendientes, cuota mensual, deuda total restante y día de cobro.
- **Integración con Dashboard:** Los fijos pendientes del mes se descuentan del saldo real disponible.

### Fase 5: Módulo de Inversiones ✅

- **Cartera de Activos:** Registro de activos financieros por ticker (acciones, ETFs). Soporte para múltiples mercados (BME, NASDAQ, NYSE, etc.).
- **Operaciones de Compra/Venta:** Historial completo con precio unitario, cantidad y comisión.
- **Dividendos:** Registro de dividendos con monto bruto, retención e importe neto.
- **Algoritmo FIFO:** Cálculo de precio medio y P&L de ventas por el método FIFO (art. 37.2 LIRPF), tal como exige la normativa fiscal española.
- **Cotizaciones en Tiempo Real:** Integración con la API de Yahoo Finance con caché de 15 minutos para evitar rate-limiting.
- **KPIs por Activo:** Vista de detalle por activo con posición abierta (lotes disponibles, precio medio, valor actual, P&L latente), historial de operaciones y rentabilidad total.
- **Informe Fiscal (Renta):** Filtro por año natural y generación de informe imprimible para la gestoría con:
  - Sección A — Ganancias Patrimoniales (casillas 1624–1631): detalle FIFO por venta
  - Sección B — Compras del ejercicio
  - Sección C — Rendimientos de Capital Mobiliario / Dividendos (casillas 0029–0031)
  - Base del Ahorro total

---

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Framework | Laravel 12, PHP 8.2+ |
| Auth | Laravel Breeze (sesión) |
| Frontend | Blade, Tailwind CSS v3, Alpine.js |
| Base de datos | SQLite (por defecto) / MySQL |
| Gráficos | Chart.js |
| Build | Vite 7 |
| Fechas | Carbon |
| Colas / Caché / Sesión | Driver `database` (sin Redis) |

---

## Instalación

```bash
# Clonar
git clone https://github.com/OteroDev/control-gastos.git
cd control-gastos

# Setup completo (instala deps, copia .env, genera clave, migra, compila assets)
composer run setup
```

### Setup manual paso a paso

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

### Desarrollo (servidor + Vite HMR)

```bash
composer run dev
```

O por separado:

```bash
php artisan serve       # HTTP en localhost:8000
npm run dev             # Vite con HMR
php artisan queue:listen
```

---

## Estructura de Datos

```
User
├── CategoriaGasto  (presupuesto_mensual, color)
│   ├── Gasto       (monto, fecha, descripcion, hash)
│   └── GastoFijo   (monto_previsto, dia_pago, meses_cobro)
├── FuenteIngreso
│   └── Ingreso     (monto, fecha, descripcion, hash)
├── ReglaCategorizacion  (palabra_clave → categoria_id)
├── Financiacion    (cuota_mensual, cuotas_pendientes, dia_cobro)
└── Activo          (ticker, nombre, sector, mercado)
    ├── OperacionInversion  (tipo: compra/venta, cantidad, precio_unitario, comision)
    └── Dividendo           (monto_bruto, retencion, monto_neto)
```

Multi-tenancy completo: todas las entidades tienen `user_id` con `onDelete('cascade')`.
