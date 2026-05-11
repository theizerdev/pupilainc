<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\CategoriaProducto;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\InventarioStock;
use Carbon\Carbon;

class ProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresa = Empresa::first();

        if (!$empresa) {
            $this->command->error('No se encontró ninguna empresa. Ejecute primero el seeder de empresas.');
            return;
        }

        $sucursales = Sucursal::where('empresa_id', $empresa->id)->get();

        if ($sucursales->isEmpty()) {
            $this->command->error('No se encontraron sucursales. Ejecute primero el seeder de sucursales.');
            return;
        }

        $productos = $this->getProductosData();

        foreach ($sucursales as $sucursal) {
            $this->command->info("Creando productos para sucursal: {$sucursal->nombre}");

            // Obtener o crear categorías para esta sucursal
            $categorias = $this->obtenerCategorias($empresa->id, $sucursal->id);

            // Obtener o crear marcas para esta sucursal
            $marcas = $this->obtenerMarcas($empresa->id, $sucursal->id);

            // Obtener o crear proveedores para esta sucursal
            $proveedores = $this->obtenerProveedores($empresa->id, $sucursal->id);

            // Obtener almacén principal
            $almacenPrincipal = Almacen::where('empresa_id', $empresa->id)
                ->where('sucursal_id', $sucursal->id)
                ->where('es_principal', true)
                ->first();

            if (!$almacenPrincipal) {
                $this->command->warn("No se encontró almacén principal para la sucursal {$sucursal->nombre}. Saltando asignación de stock.");
            }

            foreach ($productos as $productoData) {
                // Asignar categoría, marca y proveedor aleatorios si no están especificados
                if (empty($productoData['categoria_producto_id'])) {
                    $productoData['categoria_producto_id'] = $categorias->random()->id;
                }

                if (empty($productoData['marca_id'])) {
                    $productoData['marca_id'] = $marcas->random()->id;
                }

                if (empty($productoData['proveedor_id'])) {
                    $productoData['proveedor_id'] = $proveedores->random()->id;
                }

                // Guardar cantidad inicial antes de crear el producto
                $cantidadInicial = $productoData['stock_inicial'] ?? 50;
                unset($productoData['stock_inicial']);

                $producto = Producto::create(array_merge($productoData, [
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                ]));

                // Crear registro de stock en el almacén principal
                if ($almacenPrincipal) {
                    InventarioStock::create([
                        'producto_id' => $producto->id,
                        'almacen_id' => $almacenPrincipal->id,
                        'cantidad' => $cantidadInicial,
                    ]);
                }
            }
        }

        $this->command->info('Productos creados exitosamente!');
    }

    private function obtenerCategorias($empresaId, $sucursalId)
    {
        $categoriasNombres = [
            'Analgésicos',
            'Antibióticos',
            'Antiinflamatorios',
            'Antihistamínicos',
            'Vitaminas y Suplementos',
            'Equipos Médicos',
            'Material de Curación',
            'Instrumental Médico',
            'Desinfectantes',
            'Productos de Higiene',
        ];

        $categorias = collect();

        foreach ($categoriasNombres as $nombre) {
            $categoria = CategoriaProducto::firstOrCreate(
                [
                    'nombre' => $nombre,
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId
                ],
                [
                    'descripcion' => "Categoría de {$nombre}",
                    'status' => true,
                    'color' => '#3B82F6'
                ]
            );
            $categorias->push($categoria);
        }

        return $categorias;
    }

    private function obtenerMarcas($empresaId, $sucursalId)
    {
        $marcasNombres = [
            'Bayer',
            'Pfizer',
            'Johnson & Johnson',
            'Roche',
            'Novartis',
            'GlaxoSmithKline',
            'Sanofi',
            'Merck',
            'Abbott',
            '3M',
        ];

        $marcas = collect();

        foreach ($marcasNombres as $nombre) {
            $marca = Marca::firstOrCreate(
                [
                    'nombre' => $nombre,
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId
                ],
                [
                    'descripcion' => "Marca {$nombre}",
                    'status' => true
                ]
            );
            $marcas->push($marca);
        }

        return $marcas;
    }

    private function obtenerProveedores($empresaId, $sucursalId)
    {
        $proveedoresData = [
            ['nombre' => 'Distribuidora Médica Nacional', 'telefono' => '555-0101'],
            ['nombre' => 'Suministros Farmacéuticos SA', 'telefono' => '555-0102'],
            ['nombre' => 'Importadora de Equipos Médicos', 'telefono' => '555-0103'],
            ['nombre' => 'Laboratorios Unidos', 'telefono' => '555-0104'],
            ['nombre' => 'Distribuidora de Insumos Médicos', 'telefono' => '555-0105'],
        ];

        $proveedores = collect();

        foreach ($proveedoresData as $data) {
            $proveedor = Proveedor::firstOrCreate(
                [
                    'nombre' => $data['nombre'],
                    'empresa_id' => $empresaId,
                    'sucursal_id' => $sucursalId
                ],
                [
                    'telefono' => $data['telefono'],
                    'status' => true
                ]
            );
            $proveedores->push($proveedor);
        }

        return $proveedores;
    }

    private function getProductosData(): array
    {
        return [
            // ANALGÉSICOS
            [
                'nombre' => 'Paracetamol 500mg',
                'codigo' => 'MED-PARA-500',
                'sku' => 'PARA500',
                'descripcion' => 'Tabletas de paracetamol 500mg, analgésico y antipirético',
                'unidad_medida' => 'caja',
                'precio_costo' => 2.50,
                'precio_venta' => 4.99,
                'stock_minimo' => 20,
                'stock_maximo' => 200,
                'punto_reorden' => 50,
                'stock_inicial' => 150,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Ibuprofeno 400mg',
                'codigo' => 'MED-IBU-400',
                'sku' => 'IBU400',
                'descripcion' => 'Tabletas de ibuprofeno 400mg, antiinflamatorio no esteroideo',
                'unidad_medida' => 'caja',
                'precio_costo' => 3.20,
                'precio_venta' => 6.50,
                'stock_minimo' => 15,
                'stock_maximo' => 150,
                'punto_reorden' => 40,
                'stock_inicial' => 120,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Aspirina 100mg',
                'codigo' => 'MED-ASP-100',
                'sku' => 'ASP100',
                'descripcion' => 'Tabletas de ácido acetilsalicílico 100mg',
                'unidad_medida' => 'caja',
                'precio_costo' => 1.80,
                'precio_venta' => 3.50,
                'stock_minimo' => 25,
                'stock_maximo' => 250,
                'punto_reorden' => 60,
                'stock_inicial' => 200,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // ANTIBIÓTICOS
            [
                'nombre' => 'Amoxicilina 500mg',
                'codigo' => 'MED-AMOX-500',
                'sku' => 'AMOX500',
                'descripcion' => 'Cápsulas de amoxicilina 500mg, antibiótico de amplio espectro',
                'unidad_medida' => 'caja',
                'precio_costo' => 5.50,
                'precio_venta' => 11.99,
                'stock_minimo' => 10,
                'stock_maximo' => 100,
                'punto_reorden' => 30,
                'stock_inicial' => 80,
                'es_medicamento' => true,
                'requiere_receta' => true,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Azitromicina 500mg',
                'codigo' => 'MED-AZI-500',
                'sku' => 'AZI500',
                'descripcion' => 'Tabletas de azitromicina 500mg, antibiótico macrólido',
                'unidad_medida' => 'caja',
                'precio_costo' => 8.00,
                'precio_venta' => 15.99,
                'stock_minimo' => 10,
                'stock_maximo' => 80,
                'punto_reorden' => 25,
                'stock_inicial' => 60,
                'es_medicamento' => true,
                'requiere_receta' => true,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // ANTIINFLAMATORIOS
            [
                'nombre' => 'Diclofenaco 50mg',
                'codigo' => 'MED-DIC-50',
                'sku' => 'DIC50',
                'descripcion' => 'Tabletas de diclofenaco sódico 50mg',
                'unidad_medida' => 'caja',
                'precio_costo' => 3.80,
                'precio_venta' => 7.50,
                'stock_minimo' => 15,
                'stock_maximo' => 120,
                'punto_reorden' => 40,
                'stock_inicial' => 100,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Naproxeno 250mg',
                'codigo' => 'MED-NAP-250',
                'sku' => 'NAP250',
                'descripcion' => 'Tabletas de naproxeno 250mg, antiinflamatorio',
                'unidad_medida' => 'caja',
                'precio_costo' => 4.20,
                'precio_venta' => 8.99,
                'stock_minimo' => 12,
                'stock_maximo' => 100,
                'punto_reorden' => 35,
                'stock_inicial' => 85,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // ANTIHISTAMÍNICOS
            [
                'nombre' => 'Loratadina 10mg',
                'codigo' => 'MED-LOR-10',
                'sku' => 'LOR10',
                'descripcion' => 'Tabletas de loratadina 10mg, antihistamínico',
                'unidad_medida' => 'caja',
                'precio_costo' => 2.80,
                'precio_venta' => 5.99,
                'stock_minimo' => 20,
                'stock_maximo' => 180,
                'punto_reorden' => 50,
                'stock_inicial' => 160,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Cetirizina 10mg',
                'codigo' => 'MED-CET-10',
                'sku' => 'CET10',
                'descripcion' => 'Tabletas de cetirizina 10mg, antihistamínico no sedante',
                'unidad_medida' => 'caja',
                'precio_costo' => 3.00,
                'precio_venta' => 6.50,
                'stock_minimo' => 18,
                'stock_maximo' => 160,
                'punto_reorden' => 45,
                'stock_inicial' => 140,
                'es_medicamento' => true,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // VITAMINAS Y SUPLEMENTOS
            [
                'nombre' => 'Vitamina C 1000mg',
                'codigo' => 'VIT-C-1000',
                'sku' => 'VITC1000',
                'descripcion' => 'Tabletas efervescentes de vitamina C 1000mg',
                'unidad_medida' => 'tubo',
                'precio_costo' => 4.50,
                'precio_venta' => 9.99,
                'stock_minimo' => 25,
                'stock_maximo' => 200,
                'punto_reorden' => 60,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'fecha_vencimiento' => Carbon::now()->addMonths(18)->format('Y-m-d'),
                'status' => true,
            ],
            [
                'nombre' => 'Complejo B',
                'codigo' => 'VIT-COMPB',
                'sku' => 'COMPB',
                'descripcion' => 'Cápsulas de complejo vitamínico B',
                'unidad_medida' => 'frasco',
                'precio_costo' => 5.00,
                'precio_venta' => 10.99,
                'stock_minimo' => 20,
                'stock_maximo' => 150,
                'punto_reorden' => 50,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'fecha_vencimiento' => Carbon::now()->addMonths(24)->format('Y-m-d'),
                'status' => true,
            ],
            [
                'nombre' => 'Multivitamínico',
                'codigo' => 'VIT-MULTI',
                'sku' => 'MULTI',
                'descripcion' => 'Tabletas multivitamínicas completas',
                'unidad_medida' => 'frasco',
                'precio_costo' => 6.50,
                'precio_venta' => 13.99,
                'stock_minimo' => 15,
                'stock_maximo' => 120,
                'punto_reorden' => 40,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'fecha_vencimiento' => Carbon::now()->addMonths(20)->format('Y-m-d'),
                'status' => true,
            ],

            // EQUIPOS MÉDICOS
            [
                'nombre' => 'Termómetro Digital',
                'codigo' => 'EQ-TERM-DIG',
                'sku' => 'TERMDIG',
                'descripcion' => 'Termómetro digital infrarrojo',
                'unidad_medida' => 'unidad',
                'precio_costo' => 15.00,
                'precio_venta' => 29.99,
                'stock_minimo' => 10,
                'stock_maximo' => 50,
                'punto_reorden' => 20,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Tensiómetro Digital',
                'codigo' => 'EQ-TENS-DIG',
                'sku' => 'TENSDIG',
                'descripcion' => 'Tensiómetro digital de brazo automático',
                'unidad_medida' => 'unidad',
                'precio_costo' => 35.00,
                'precio_venta' => 69.99,
                'stock_minimo' => 5,
                'stock_maximo' => 30,
                'punto_reorden' => 10,
                'stock_inicial' => 25,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Oxímetro de Pulso',
                'codigo' => 'EQ-OXIM',
                'sku' => 'OXIM',
                'descripcion' => 'Oxímetro de pulso digital portátil',
                'unidad_medida' => 'unidad',
                'precio_costo' => 12.00,
                'precio_venta' => 24.99,
                'stock_minimo' => 8,
                'stock_maximo' => 40,
                'punto_reorden' => 15,
                'stock_inicial' => 35,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // MATERIAL DE CURACIÓN
            [
                'nombre' => 'Gasas Estériles 10x10cm',
                'codigo' => 'MAT-GASA-10',
                'sku' => 'GASA10',
                'descripcion' => 'Paquete de gasas estériles 10x10cm, 10 unidades',
                'unidad_medida' => 'paquete',
                'precio_costo' => 1.50,
                'precio_venta' => 3.50,
                'stock_minimo' => 50,
                'stock_maximo' => 500,
                'punto_reorden' => 100,
                'stock_inicial' => 400,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Vendas Elásticas 10cm',
                'codigo' => 'MAT-VEND-10',
                'sku' => 'VEND10',
                'descripcion' => 'Venda elástica de 10cm de ancho',
                'unidad_medida' => 'unidad',
                'precio_costo' => 2.00,
                'precio_venta' => 4.50,
                'stock_minimo' => 40,
                'stock_maximo' => 300,
                'punto_reorden' => 80,
                'stock_inicial' => 250,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Algodón Hidrófilo 100g',
                'codigo' => 'MAT-ALG-100',
                'sku' => 'ALG100',
                'descripcion' => 'Algodón hidrófilo esterilizado 100g',
                'unidad_medida' => 'paquete',
                'precio_costo' => 1.20,
                'precio_venta' => 2.99,
                'stock_minimo' => 60,
                'stock_maximo' => 400,
                'punto_reorden' => 120,
                'stock_inicial' => 350,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Cinta Adhiva Micropore',
                'codigo' => 'MAT-CINT-MICRO',
                'sku' => 'MICRO',
                'descripcion' => 'Cinta adhesiva microporosa 2.5cm x 9m',
                'unidad_medida' => 'rollo',
                'precio_costo' => 2.50,
                'precio_venta' => 5.50,
                'stock_minimo' => 30,
                'stock_maximo' => 200,
                'punto_reorden' => 60,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // INSTRUMENTAL MÉDICO
            [
                'nombre' => 'Estetoscopio',
                'codigo' => 'INST-ESTET',
                'sku' => 'ESTET',
                'descripcion' => 'Estetoscopio clínico de doble campana',
                'unidad_medida' => 'unidad',
                'precio_costo' => 25.00,
                'precio_venta' => 49.99,
                'stock_minimo' => 5,
                'stock_maximo' => 25,
                'punto_reorden' => 10,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Riñonera de Acero Inoxidable',
                'codigo' => 'INST-RINON',
                'sku' => 'RINON',
                'descripcion' => 'Riñonera de acero inoxidable para procedimientos',
                'unidad_medida' => 'unidad',
                'precio_costo' => 8.00,
                'precio_venta' => 16.99,
                'stock_minimo' => 10,
                'stock_maximo' => 50,
                'punto_reorden' => 20,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],

            // DESINFECTANTES
            [
                'nombre' => 'Alcohol Etílico 70% 1L',
                'codigo' => 'DES-ALC-70',
                'sku' => 'ALC70',
                'descripcion' => 'Alcohol etílico al 70% para desinfección, 1 litro',
                'unidad_medida' => 'botella',
                'precio_costo' => 3.50,
                'precio_venta' => 7.50,
                'stock_minimo' => 30,
                'stock_maximo' => 200,
                'punto_reorden' => 60,
                'stock_inicial' => 180,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Yodopovidona 10% 500ml',
                'codigo' => 'DES-YODO-10',
                'sku' => 'YODO10',
                'descripcion' => 'Solución de yodopovidona al 10%, 500ml',
                'unidad_medida' => 'botella',
                'precio_costo' => 4.00,
                'precio_venta' => 8.99,
                'stock_minimo' => 25,
                'stock_maximo' => 150,
                'punto_reorden' => 50,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'fecha_vencimiento' => Carbon::now()->addMonths(24)->format('Y-m-d'),
                'status' => true,
            ],
            [
                'nombre' => 'Clorhexidina 4% 250ml',
                'codigo' => 'DES-CLOR-4',
                'sku' => 'CLOR4',
                'descripcion' => 'Solución de clorhexidina al 4%, 250ml',
                'unidad_medida' => 'botella',
                'precio_costo' => 5.00,
                'precio_venta' => 10.99,
                'stock_minimo' => 20,
                'stock_maximo' => 120,
                'punto_reorden' => 40,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'fecha_vencimiento' => Carbon::now()->addMonths(18)->format('Y-m-d'),
                'status' => true,
            ],

            // PRODUCTOS DE HIGIENE
            [
                'nombre' => 'Jabón Antibacterial 500ml',
                'codigo' => 'HIG-JAB-ANT',
                'sku' => 'JABANT',
                'descripcion' => 'Jabón líquido antibacterial 500ml',
                'unidad_medida' => 'botella',
                'precio_costo' => 3.00,
                'precio_venta' => 6.50,
                'stock_minimo' => 25,
                'stock_maximo' => 180,
                'punto_reorden' => 50,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
            [
                'nombre' => 'Gel Antibacterial 250ml',
                'codigo' => 'HIG-GEL-ANT',
                'sku' => 'GELANT',
                'descripcion' => 'Gel antibacterial con alcohol 70%, 250ml',
                'unidad_medida' => 'botella',
                'precio_costo' => 2.50,
                'precio_venta' => 5.50,
                'stock_minimo' => 30,
                'stock_maximo' => 200,
                'punto_reorden' => 60,
                'es_medicamento' => false,
                'requiere_receta' => false,
                'aplica_iva' => false,
                'exento_iva' => false,
                'iva_alicuota' => 16.00,
                'status' => true,
            ],
        ];
    }
}
