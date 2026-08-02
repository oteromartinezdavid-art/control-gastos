# 💰 Control de Gastos

Aplicación web de finanzas personales construida con **Laravel 12** y **SQLite**. Permite registrar y analizar gastos e ingresos, gestionar una cartera de inversiones, controlar préstamos y suscripciones, y hacer seguimiento de presupuestos mensuales.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.2 · Laravel 12 |
| Frontend | Blade · Tailwind CSS · Alpine.js · Chart.js |
| Base de datos | SQLite |
| Autenticación | Laravel Breeze |

---

## Funcionalidades

### Dashboard
- Resumen del mes actual: total de gastos, ingresos, balance y ahorro
- Navegación por meses (← mes anterior / mes siguiente →)
- Gráfico de gastos por categoría

### Gastos
- CRUD completo con categoría, fecha, importe y descripción
- Categorías con color personalizable
- Filtrado y búsqueda

### Ingresos
- CRUD completo con fuente de ingreso, fecha e importe
- Fuentes de ingreso con color personalizable (badges coloreados)

### Importación CSV
- Importación masiva de extractos bancarios en formato CSV
- Deduplicación automática por hash MD5 (fecha + descripción + importe)
- **Reglas de importación para gastos**: asignación automática de categoría por palabra clave
- **Reglas de importación para ingresos**: asignación automática de fuente de ingreso por palabra clave
- Prioridad por especificidad: la regla con la coincidencia más larga gana
- Recategorización de registros existentes mediante comandos Artisan

### Presupuesto mensual
- Presupuesto **independiente por mes y año** (cada mes puede ser diferente)
- Añadir, editar y eliminar categorías del presupuesto de un mes concreto
- Copiar presupuesto del mes anterior con un clic
- Barras de progreso por categoría con alertas visuales (verde / ámbar / rojo)
- KPIs: presupuesto total, gastado, restante y progreso global del mes

### Gastos Fijos
- Control de suscripciones y pagos recurrentes (Netflix, gimnasio, seguros…)
- Estado activo / dado de baja
- Coste mensual total estimado

### Financiaciones (Préstamos)
- Registro de préstamos y compras a plazos
- Capital pendiente, cuota mensual y fecha de fin
- Resumen de deuda total

### Inversiones

**Cartera (Mi Cartera)**
- Registro de activos (acciones, ETFs, fondos)
- Operaciones de compra y venta con cálculo de coste FIFO
- Dividendos con retención fiscal
- Cotización en tiempo real por ticker

**Resumen Anual**
- Navegación por años (solo años con actividad)
- Cartera al inicio y fin del año (coste de adquisición FIFO)
- P&L realizado en ventas (importe + porcentaje)
- Dividendos: bruto / retención / neto por activo
- Resumen fiscal: resultado computable para la declaración
- Botón de informe fiscal → vista de operaciones del año filtrada

**Proyección de cartera**
- Simulador de interés compuesto configurable
- Parámetros: valor inicial, aportación mensual, % dividendo, % revalorización, años, reinversión de dividendos
- KPIs: valor final, total aportado, dividendos cobrados, multiplicador
- Gráfico Chart.js con valor de cartera y dividendos acumulados
- Tabla año a año

### Configuración
- **Categorías de gastos**: CRUD con color
- **Fuentes de ingresos**: CRUD con color
- **Reglas de importación (gastos)**: palabra clave → categoría
- **Reglas de importación (ingresos)**: palabra clave → fuente de ingreso

### Copias de seguridad
- Descarga manual de la base de datos SQLite
- Restauración desde copia de seguridad anterior

---

## Instalación

```bash
git clone <repo>
cd control-gastos
composer run setup
```

Esto ejecuta en un paso: `composer install`, copia `.env`, genera la clave de aplicación, ejecuta las migraciones, instala dependencias npm y compila los assets.

### Desarrollo

```bash
composer run dev
```

Lanza en paralelo: servidor PHP, queue worker, log watcher (Pail) y Vite HMR.

---

## Comandos Artisan útiles

```bash
# Migrar presupuestos del campo fijo de categorías a la nueva tabla mensual
php artisan presupuesto:migrar

# Reasignar categorías a gastos existentes según reglas de importación
php artisan gastos:recategorizar

# Reasignar fuentes a ingresos existentes según reglas de importación
php artisan ingresos:reasignar
```

---

## Estructura principal

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── GastoController.php
│   ├── IngresoController.php
│   ├── ImportacionController.php        # CSV + deduplicación + reglas
│   ├── PresupuestoController.php        # Presupuesto por mes/año
│   ├── GastoFijoController.php
│   ├── FinanciacionController.php
│   ├── InversionController.php          # Cartera + proyección
│   ├── ResumenAnualInversionController.php
│   ├── OperacionInversionController.php
│   ├── DividendoController.php
│   ├── CategoriaGastoController.php
│   ├── FuenteIngresoController.php
│   ├── ReglaController.php              # Reglas importación gastos
│   └── ReglaIngresoController.php       # Reglas importación ingresos
├── Models/
│   ├── Gasto.php · Ingreso.php
│   ├── CategoriaGasto.php · FuenteIngreso.php
│   ├── ReglaCategoria.php · ReglaFuenteIngreso.php
│   ├── PresupuestoMensual.php
│   ├── GastoFijo.php · Financiacion.php
│   ├── Inversion.php · Activo.php
│   ├── OperacionInversion.php · Dividendo.php
│   └── BackupFile.php
└── Services/
    └── InversionService.php             # FIFO, resumen anual, cartera en fecha
```

---

## Licencia

Uso personal. Sin licencia pública.
