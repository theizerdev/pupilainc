# Seeder de Productos

## Descripción

El `ProductosSeeder` crea productos e insumos médicos de ejemplo para el sistema de inventario, incluyendo medicamentos, equipos médicos, material de curación y otros productos necesarios para una clínica médica.

## Características

### Tipos de Productos Incluidos

1. **Analgésicos** (3 productos)
   - Paracetamol 500mg
   - Ibuprofeno 400mg
   - Aspirina 100mg

2. **Antibióticos** (2 productos)
   - Amoxicilina 500mg (requiere receta)
   - Azitromicina 500mg (requiere receta)

3. **Antiinflamatorios** (2 productos)
   - Diclofenaco 50mg
   - Naproxeno 250mg

4. **Antihistamínicos** (2 productos)
   - Loratadina 10mg
   - Cetirizina 10mg

5. **Vitaminas y Suplementos** (3 productos)
   - Vitamina C 1000mg
   - Complejo B
   - Multivitamínico

6. **Equipos Médicos** (3 productos)
   - Termómetro Digital
   - Tensiómetro Digital
   - Oxímetro de Pulso

7. **Material de Curación** (4 productos)
   - Gasas Estériles 10x10cm
   - Vendas Elásticas 10cm
   - Algodón Hidrófilo 100g
   - Cinta Adhesiva Micropore

8. **Instrumental Médico** (2 productos)
   - Estetoscopio
   - Riñonera de Acero Inoxidable

9. **Desinfectantes** (3 productos)
   - Alcohol Etílico 70% 1L
   - Yodopovidona 10% 500ml
   - Clorhexidina 4% 250ml

10. **Productos de Higiene** (2 productos)
    - Jabón Antibacterial 500ml
    - Gel Antibacterial 250ml

**Total: 26 productos diferentes**

## Dependencias

El seeder requiere que existan previamente:
- **Empresas** (EmpresaSeeder)
- **Sucursales** (SucursalSeeder)
- **Categorías de Producto** (se crean automáticamente si no existen)
- **Marcas** (se crean automáticamente si no existen)
- **Proveedores** (se crean automáticamente si no existen)

## Categorías Automáticas

El seeder crea automáticamente las siguientes categorías si no existen:
- Analgésicos
- Antibióticos
- Antiinflamatorios
- Antihistamínicos
- Vitaminas y Suplementos
- Equipos Médicos
- Material de Curación
- Instrumental Médico
- Desinfectantes
- Productos de Higiene

## Marcas Automáticas

Se crean las siguientes marcas farmacéuticas:
- Bayer
- Pfizer
- Johnson & Johnson
- Roche
- Novartis
- GlaxoSmithKline
- Sanofi
- Merck
- Abbott
- 3M

## Proveedores Automáticos

Se crean 5 proveedores de ejemplo con información básica.

## Características de los Productos

### Campos Configurados

Cada producto incluye:
- **nombre**: Nombre descriptivo del producto
- **codigo**: Código único del producto (formato: XXX-YYY-ZZZ)
- **sku**: SKU comercial
- **descripcion**: Descripción detallada
- **categoria_producto_id**: Categoría asignada
- **marca_id**: Marca del fabricante
- **proveedor_id**: Proveedor principal
- **unidad_medida**: Unidad de medida (caja, frasco, unidad, etc.)
- **precio_costo**: Precio de compra
- **precio_venta**: Precio de venta al público
- **stock_minimo**: Stock mínimo antes de alerta crítica
- **stock_maximo**: Stock máximo recomendado
- **punto_reorden**: Punto para reorderar stock
- **stock_inicial**: Cantidad inicial de stock en el almacén principal
- **fecha_vencimiento**: Fecha de vencimiento (para productos perecederos)
- **es_medicamento**: Booleano que indica si es medicamento
- **requiere_receta**: Booleano para medicamentos que requieren receta
- **aplica_iva**: Booleano para aplicar IVA
- **exento_iva**: Booleano para productos exentos de IVA
- **iva_alicuota**: Porcentaje de IVA (16% por defecto)
- **status**: Estado activo/inactivo

### Stock Inicial Configurado

El seeder crea automáticamente registros de stock en el **Almacén Principal** con cantidades realistas:

- **Analgésicos**: 120-200 unidades (alta rotación)
- **Antibióticos**: 60-80 unidades (controlados)
- **Antiinflamatorios**: 85-100 unidades
- **Antihistamínicos**: 140-160 unidades
- **Vitaminas**: 110-180 unidades
- **Equipos Médicos**: 25-40 unidades (baja rotación, alto valor)
- **Material de Curación**: 250-400 unidades (alto consumo)
- **Instrumental Médico**: 50 unidades
- **Desinfectantes**: 100-180 unidades
- **Productos de Higiene**: 50 unidades

**Stock Total Aproximado**: ~2,885 unidades distribuidas en 26 productos

### Márgenes de Ganancia

Los productos tienen márgenes de ganancia variables:
- **Medicamentos genéricos**: ~50-100%
- **Equipos médicos**: ~80-100%
- **Material de curación**: ~100-130%
- **Vitaminas**: ~100-120%
- **Desinfectantes**: ~100-120%

### Fechas de Vencimiento

Los productos perecederos incluyen fechas de vencimiento:
- **Vitaminas**: 18-24 meses desde la fecha actual
- **Desinfectantes**: 18-24 meses desde la fecha actual
- **Medicamentos**: Sin fecha específica (depende del lote)

## Ejecución

### Ejecutar el Seeder

```bash
php artisan db:seed --class=ProductosSeeder
```

### Ejecutar con Todos los Seeders

```bash
php artisan db:seed
```

Esto ejecutará todos los seeders en el orden definido en `DatabaseSeeder.php`.

## Notas Importantes

1. **Multitenancy**: Los productos, categorías, marcas y proveedores se crean para cada sucursal de la empresa encontrada
2. **Códigos Únicos**: Cada producto tiene un código único siguiendo el patrón: `[TIPO]-[NOMBRE]-[DOSIS]`
3. **Stock**: El seeder solo crea los productos, no inicializa el stock en inventario
4. **Precios**: Los precios son ejemplos y deben ajustarse según la realidad del mercado local
5. **IVA**: Todos los productos tienen IVA del 16% configurado por defecto
6. **Dependencias**: Categorías, marcas y proveedores incluyen `empresa_id` y `sucursal_id` para cumplir con el esquema de multitenancy

## Personalización

Para agregar más productos, editar el método `getProductosData()` en el archivo `ProductosSeeder.php` y agregar nuevos arrays con la estructura deseada.

### Ejemplo de Producto Nuevo

```php
[
    'nombre' => 'Nuevo Producto',
    'codigo' => 'XXX-YYY-ZZZ',
    'sku' => 'SKU123',
    'descripcion' => 'Descripción del producto',
    'unidad_medida' => 'caja',
    'precio_costo' => 5.00,
    'precio_venta' => 10.00,
    'stock_minimo' => 10,
    'stock_maximo' => 100,
    'punto_reorden' => 25,
    'es_medicamento' => false,
    'requiere_receta' => false,
    'aplica_iva' => true,
    'exento_iva' => false,
    'iva_alicuota' => 16.00,
    'status' => true,
],
```

## Archivos Relacionados

- **Seeder**: `database/seeders/ProductosSeeder.php`
- **Modelo**: `app/Models/Producto.php`
- **Migración**: `database/migrations/2026_04_02_000003_create_productos_table.php`
- **DatabaseSeeder**: `database/seeders/DatabaseSeeder.php`
