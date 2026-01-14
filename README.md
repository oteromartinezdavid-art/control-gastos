# 💰 Control de Finanzas Personal - Laravel 11
Sistema integral de gestión financiera diseñado para centralizar y automatizar el control de ingresos, gastos, préstamos y presupuestos en una sola plataforma. Este proyecto nace de la necesidad de sustituir múltiples archivos Excel por una herramienta robusta, dinámica y visual.

# 🚀 Funcionalidades Principales
* Fase 1: Dashboard Inteligente (Completado ✅)
* Panel de Control Visual: Resumen de ingresos, gastos y saldo total.
* Navegación Mensual: Sistema de filtrado por meses con flechas de navegación y selectores dinámicos.
* Gráficos Estadísticos: Visualización de la distribución de gastos por categoría mediante gráficos de tipo "Donut" (Chart.js).
* Interfaz Personalizada: Diseño limpio con Tailwind CSS utilizando una identidad visual propia (Azul Marino & Naranja).

# 🛠️ Próximas Implementaciones (Roadmap)
* Fase 2: Gestión dinámica de categorías de gastos y fuentes de ingresos desde la base de datos.
* Fase 3: Importador automático de archivos CSV bancarios para automatización de registros.
* Fase 4: Módulo de préstamos con gestión de amortizaciones parciales y recalculo de cuotas/plazos.
* Fase 5: Sistema de presupuestos mensuales con alertas de límites de gasto.

# 🛠️ Stack Tecnológico
* Framework: Laravel 11
* Frontend: Blade, Tailwind CSS, Alpine.js (Starter kit: Laravel Breeze)
* Base de Datos: MySQL
* Gráficos: Chart.js
* Servidor Local: Laragon

# ⚙️ Instalación y Configuración

**Clonar el repositorio:**

<pre>git clone https://github.com/tu-usuario/control-gastos.git</pre>

*Instalar dependencias:*

<pre>composer install
npm install && npm run build</pre>

**Configurar el entorno:**

* Copiar .env.example a .env
* Configurar las credenciales de la base de datos en el .env.
* Generar la clave de aplicación: php artisan key:generate

**Migraciones:**

<pre>php artisan migrate</pre>