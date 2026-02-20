# Sistema de Proceso de Consulta Médica

## Descripción General

Sistema de gestión de consultas médicas por pasos con autoguardado automático para prevenir pérdida de datos por fallas de conexión.

## Estructura de Tablas

### 1. `consulta_evaluaciones`
Almacena la evaluación médica (Paso 2)
- `enfermedad_actual`: Descripción de la enfermedad actual
- `examen_fisico`: Resultados del examen físico
- `conclusion`: Conclusión médica
- `observaciones_adicionales`: Observaciones adicionales

### 2. `consulta_estudios`
Almacena laboratorios y procedimientos (Paso 3)
- `tipo_estudio`: imagen | laboratorio | otros
- `nombre_estudio`: Nombre del estudio solicitado
- `indicaciones`: Indicaciones para el estudio
- `orden`: Orden de los estudios

### 3. `consulta_tratamientos`
Almacena tratamientos y medicamentos (Paso 4)
- `medicamento`: Nombre del medicamento
- `indicaciones`: Indicaciones del tratamiento
- `orden`: Orden de los tratamientos

## Modelos Eloquent

- `ConsultaEvaluacion`: Relación 1:1 con Consulta
- `ConsultaEstudio`: Relación 1:N con Consulta
- `ConsultaTratamiento`: Relación 1:N con Consulta

## Componente Livewire

**Ubicación**: `App\Livewire\Admin\Consulta\ProcesoConsulta`

### Características

1. **Navegación por Pasos**: 4 pasos secuenciales
   - Paso 1: Visualización del cuestionario de preconsulta
   - Paso 2: Registro de evaluación con autoguardado
   - Paso 3: Agregar estudios (estilo carrito)
   - Paso 4: Agregar tratamientos (estilo carrito)

2. **Autoguardado Inteligente**
   - Paso 2: Autoguardado con debounce de 1 segundo
   - Pasos 3 y 4: Guardado inmediato al agregar items

3. **Gestión de Items**
   - Agregar estudios/tratamientos dinámicamente
   - Eliminar items con confirmación
   - Orden automático de items

## Rutas

```php
// Ruta principal
Route::get('/admin/consulta/{consultaId}/proceso', ProcesoConsulta::class)
    ->name('admin.consulta.proceso');
```

## Uso

### Desde la vista de consultas en consultorio

```blade
<a href="{{ route('admin.consulta.proceso', $consulta->id) }}" 
   class="btn blue waves-effect">
    <i class="material-icons left">play_arrow</i>
    Iniciar Proceso
</a>
```

### Verificación de permisos

Solo médicos con permiso `access consultas` pueden acceder.

## Flujo de Trabajo

1. **Médico accede** a `/admin/gestion/consultas/en-consultorio`
2. **Selecciona consulta** y hace clic en "Iniciar Proceso"
3. **Paso 1**: Revisa el cuestionario de preconsulta del paciente
4. **Paso 2**: Registra evaluación (autoguardado cada segundo)
5. **Paso 3**: Agrega estudios necesarios (guardado inmediato)
6. **Paso 4**: Agrega tratamientos (guardado inmediato)
7. **Finaliza consulta**: Cambia estado a "finalizada"

## Ventajas del Sistema

✅ **Sin pérdida de datos**: Autoguardado previene pérdida por desconexión
✅ **Tablas separadas**: Mejor organización y escalabilidad
✅ **Multitenancy**: Soporte para múltiples empresas/sucursales
✅ **Auditoría**: Registro de quién creó/modificó cada registro
✅ **UX mejorada**: Navegación fluida entre pasos
✅ **Carrito de compras**: Agregar/eliminar items dinámicamente

## Próximos Pasos Sugeridos

1. Agregar generación de recetas médicas en PDF
2. Implementar firma digital del médico
3. Agregar impresión de órdenes de estudios
4. Notificaciones por WhatsApp de recetas/estudios
5. Historial de consultas del paciente
