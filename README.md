# 💰 Control de Finanzas Personal - Laravel 11

Sistema integral de gestión financiera diseñado para centralizar y automatizar el control de ingresos, gastos y presupuestos en una sola plataforma. Este proyecto sustituye los flujos de trabajo en Excel por una aplicación web robusta, dinámica y segura.

---

## 🚀 Estado del Proyecto

### Fase 1: Dashboard e Interfaz Base (Completado ✅)
* **Panel de Control Visual:** Resumen en tiempo real de ingresos, gastos y saldo neto disponible.
* **Navegación Temporal:** Sistema de filtrado por mes y año mediante objetos **Carbon**, con navegación intuitiva entre periodos.
* **Gráficos Estadísticos:** Visualización de la distribución de gastos por categoría mediante gráficos de tipo "Doughnut" (**Chart.js**).
* **Diseño UI/UX:** Interfaz limpia y profesional utilizando **Tailwind CSS** con una paleta de colores personalizada (Indigo, Azul y Naranja).

### Fase 2: Gestión Dinámica y CRUDs (Completado ✅)
* **Control de Categorías:** Gestión completa de categorías de gastos, incluyendo asignación de colores y presupuestos mensuales.
* **Fuentes de Ingresos:** Administración dinámica de las procedencias del dinero desde la base de datos.
* **Arquitectura Unificada:** Implementación de formularios compartidos (**Partial Views**) para creación y edición, optimizando la mantenibilidad del código.
* **Integridad Referencial:** Lógica de validación en controladores que impide el borrado de categorías con registros vinculados para proteger la consistencia del historial.

---

## 🛠️ Próximas Implementaciones (Roadmap)

### 🔹 Fase 3: Optimización del Importador Bancario
* **Estado actual:** Funcional para la carga masiva de movimientos mediante archivos CSV (optimizado para Bankinter).
* **Objetivo:** Implementar un motor de **categorización automática** basado en reglas de palabras clave (ej. "MERCADONA" ➔ "Alimentación") para minimizar la intervención manual.

### 🔹 Fase 4: Sistema de Presupuestos Mensuales
* Creación de una pantalla de generación de presupuestos por categoría.
* Implementación de alertas visuales y barras de progreso para detectar excedentes de gasto en tiempo real.

### 🔹 Fase 5: Módulo de Préstamos
* Gestión de préstamos activos con seguimiento de amortizaciones parciales.
* Cálculo automático de cuotas restantes y plazos de finalización.

---

## 💻 Stack Tecnológico

* **Framework:** Laravel 11
* **Frontend:** Blade, Tailwind CSS, Alpine.js (Laravel Breeze)
* **Base de Datos:** MySQL
* **Gráficos:** Chart.js
* **Librerías auxiliares:** Carbon (Gestión de fechas)

---

## ⚙️ Instalación y Configuración

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/tu-usuario/control-gastos.git](https://github.com/tu-usuario/control-gastos.git)

*Instalar dependencias:*

<pre>composer install
npm install && npm run build</pre>

**Configurar el entorno:**

* Copiar .env.example a .env
* Configurar las credenciales de la base de datos en el .env.
* Generar la clave de aplicación: php artisan key:generate

**Migraciones:**

<pre>php artisan migrate</pre>